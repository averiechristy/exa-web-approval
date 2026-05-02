<?php

namespace App\Http\Controllers;

use App\Http\Requests\WorkflowRequest;
use App\Models\Division;
use App\Models\Organization;
use App\Models\Role;
use App\Models\Workflow;
use App\Models\WorkflowStep;
use App\Services\WorkflowService;
use Illuminate\Http\Request;

class WorkflowController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function __construct(private WorkflowService $workflowService)
    {
    }

    public function index(Request $request)
    {
        $perPage = $request->perPage;
        $search = $request->search;
        $workflow = $this->workflowService->getWorkflow($perPage ?? 10, $search);

        return view('workflow.index',[
            'workflow' => $workflow,
            'divisions' => Division::all(),
            'organization' => Organization::all(),
            'roles' => Role::all(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(WorkflowRequest $request)
    {
        $this->workflowService->createWorkflow($request->validated());

        return redirect()->route('workflow.index')
            ->with('success', 'Success Add Data');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $workflow = Workflow::with('workflowstep')->findOrFail($id);

        return response()->json([
            'id' => $workflow->id,
            'document_type' => $workflow->document_type,
            'organization_id' => $workflow->organization_id,
            'steps' => $workflow->workflowstep->map(function ($s) {
                return [
                    'tier' => $s->tier,
                    'division_id' => $s->division_id,
                    'sla_days' => $s->sla_days
                ];
            })
        ]);
    }
    /**
     * Show the form for editing the specified resource.
     */
public function edit($id)
{
    return Workflow::with('workflowstep')->findOrFail($id);
}
    /**
     * Update the specified resource in storage.
     */
    public function update(WorkflowRequest $request, Workflow $workflow)
    {
        $this->workflowService->updateWorkflow(
            $workflow,
            $request->validated()
        );

        return redirect()->route('workflow.index')
            ->with('success', 'Success Update Data');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Workflow $workflow)
    {
        $this->workflowService->deleteWorkflow($workflow);

        return redirect()->route('workflow.index')
            ->with('success', 'Success Delete Data');
    }

    public function getSteps($id)
    {
        $steps = WorkflowStep::where('workflow_id', $id)
            ->orderBy('tier')
            ->get();

        return response()->json($steps);
    }
}
