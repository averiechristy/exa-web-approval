<?php

namespace App\Services;

use App\Jobs\LogActivityJob;
use App\Models\Workflow;
use App\Models\WorkflowStep;

class WorkflowService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function getWorkflow(int $perPage = 10, $search = null)
    {
        $query = Workflow::with([
            'workflowstep.division',
            'workflowstep.role', 
            'organization'
        ]);

        if ($search) {
            $query->whereRaw(
                'LOWER(document_type) LIKE ?',
                ['%' . strtolower($search) . '%']
            );
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function createWorkflow($data)
    {
        $workflow = Workflow::create([
            'document_type' => $data['document_type'],
            'organization_id' => $data['organization_id'],
        ]);

        foreach ($data['steps'] as $step) {
            WorkflowStep::create([
                'workflow_id' => $workflow->id,
                'tier' => $step['tier'],
                'division_id' => $step['division_id'],
                'sla_days' => $step['sla_days'] ?? null,
                'min_role_level' => $step['min_role_level'],
            ]);
        }

        $workflow->load([
            'organization',
            'steps.division',
        ]);

        LogActivityJob::dispatchSync(
            logName: 'workflow',
            causedBy: auth()->user(),
            performedOn: $workflow,
            event: 'workflow.created',
            description: 'Create Workflow',
            properties: [
                'attributes' => [
                    'document_type' => $workflow->document_type,
                    'organization' => $workflow->organization?->organization_name,

                    'steps' => $workflow->steps->map(function ($step) {
                        return [
                            'tier' => $step->tier,
                            'division' => $step->division?->division_name,
                            'sla_days' => $step->sla_days,
                            'min_role_level' => $step->min_role_level,
                        ];
                    })->values()->toArray(),
                ],
            ],
        );

        return $workflow;
    }

    public function updateWorkflow(Workflow $workflow, array $data): Workflow
    {
        $workflow->load([
            'organization',
            'steps.division',
        ]);

        $oldData = [
            'document_type' => $workflow->document_type,
            'organization' => $workflow->organization?->organization_name,
            'steps' => $workflow->steps->map(function ($step) {
                return [
                    'tier' => $step->tier,
                    'division' => $step->division?->division_name,
                    'sla_days' => $step->sla_days,
                    'min_role_level' => $step->min_role_level,
                ];
            })->values()->toArray(),
        ];

        $workflow->update([
            'document_type' => $data['document_type'],
            'organization_id' => $data['organization_id'],
        ]);

        WorkflowStep::where('workflow_id', $workflow->id)->delete();

        foreach ($data['steps'] as $step) {
            WorkflowStep::create([
                'workflow_id' => $workflow->id,
                'tier' => $step['tier'],
                'division_id' => $step['division_id'],
                'sla_days' => $step['sla_days'] ?? null,
                'min_role_level' => $step['min_role_level'],
            ]);
        }

        $workflow->refresh()->load([
            'organization',
            'steps.division',
        ]);

        $newData = [
            'document_type' => $workflow->document_type,
            'organization' => $workflow->organization?->organization_name,
            'steps' => $workflow->steps->map(function ($step) {
                return [
                    'tier' => $step->tier,
                    'division' => $step->division?->division_name,
                    'sla_days' => $step->sla_days,
                    'min_role_level' => $step->min_role_level,
                ];
            })->values()->toArray(),
        ];

        LogActivityJob::dispatchSync(
            logName: 'workflow',
            causedBy: auth()->user(),
            performedOn: $workflow,
            event: 'workflow.updated',
            description: 'Update Workflow',
            properties: [
                'old' => $oldData,
                'attributes' => $newData,
            ],
        );

        return $workflow;
    }

    public function deleteWorkflow(Workflow $workflow)
    {

        if (
            $workflow->documents()->exists()
        ) {
            throw new \Exception('Workflow cannot be deleted because it is already used.');
        }
        $workflow->load([
            'organization',
            'steps.division',
        ]);

        $oldData = [
            'document_type' => $workflow->document_type,
            'organization' => $workflow->organization?->organization_name,
            'steps' => $workflow->steps->map(function ($step) {
                return [
                    'tier' => $step->tier,
                    'division' => $step->division?->division_name,
                    'sla_days' => $step->sla_days,
                    'min_role_level' => $step->min_role_level,
                ];
            })->values()->toArray(),
        ];

        LogActivityJob::dispatchSync(
            logName: 'workflow',
            causedBy: auth()->user(),
            performedOn: $workflow,
            event: 'workflow.deleted',
            description: 'Delete Workflow',
            properties: [
                'old' => $oldData,
            ],
        );

        WorkflowStep::where('workflow_id', $workflow->id)->delete();

        return $workflow->delete();
    }
}
