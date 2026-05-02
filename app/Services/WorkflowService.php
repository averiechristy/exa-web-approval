<?php

namespace App\Services;

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
            'document_type'   => $data['document_type'],
            'organization_id' => $data['organization_id'],
        ]);

        // 2. Insert steps
        foreach ($data['steps'] as $step) {
            WorkflowStep::create([
                'workflow_id' => $workflow->id,
                'tier'         => $step['tier'],
                'division_id'  => $step['division_id'],
                'sla_days'     => $step['sla_days'] ?? null,
                'min_role_level' => $step['min_role_level'],
            ]);
        }

        return $workflow;
    }

    public function updateWorkflow(Workflow $workflow, array $data): Workflow
    {
        $workflow->update([
            'document_type'   => $data['document_type'],
            'organization_id' => $data['organization_id'],
        ]);

        // 2. Hapus step lama
       WorkflowStep::where('workflow_id', $workflow->id)->delete();

        // 3. Insert ulang steps baru
        foreach ($data['steps'] as $step) {
            WorkflowStep::create([
                'workflow_id' => $workflow->id,
                'tier'         => $step['tier'],
                'division_id'  => $step['division_id'],
                'sla_days'     => $step['sla_days'] ?? null,
                'min_role_level' => $step['min_role_level'],
            ]);
        }

        return $workflow;

    }

    public function deleteWorkflow(Workflow $workflow)
    {
        WorkflowStep::where('workflow_id', $workflow->id)->delete();

        return $workflow->delete();
    }
}
