<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\Folder;
use App\Models\Organization;
use App\Models\User;
use App\Models\UserAccess;
use App\Models\Workflow;
use App\Models\WorkflowStep;
use Illuminate\Http\Request;

class UploadController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        $isSuperAdmin = $user->system_role_id == 1;
        $activeOrganizationId = session('active_organization_id');


        // ORGANIZATION
        $organizations = $isSuperAdmin
            ? Organization::all()
            : Organization::whereHas('useraccess', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->get();

        // DIVISION
        $divisions = Division::all();
    

        // FOLDER

        $rootFoldersQuery = Folder::with('children')
            ->whereNull('parent_id');

        if (!$isSuperAdmin) {
            $rootFoldersQuery->where('organization_id', $activeOrganizationId);
        }

        $rootFolders = $rootFoldersQuery->get();

        $folderOptions = $this->buildFolderOptions($rootFolders);

        $documentTypes = [];

        if ($activeOrganizationId) {
            $documentTypes = Workflow::where('organization_id', $activeOrganizationId)
                ->pluck('document_type', 'id');
        }

        return view('upload.index', compact(
            'organizations',
            'divisions',
            'folderOptions',
            'isSuperAdmin',
            'documentTypes'
        ));
    }

    private function buildFolderOptions($folders, $prefix = '')
    {
        $result = [];

        foreach ($folders as $folder) {

            $name = $prefix 
                ? $prefix . ' / ' . $folder->folder_name
                : $folder->folder_name;

            $result[] = [
                'id' => $folder->id,
                'name' => $name
            ];

            if ($folder->children && $folder->children->count()) {
                $children = $this->buildFolderOptions($folder->children, $name);
                $result = array_merge($result, $children);
            }
        }

        return $result;
    }

    public function getByOrganization($orgId)
    {
        $folders = Folder::with('children')
            ->where('organization_id', $orgId)
            ->whereNull('parent_id')
            ->get();

        return response()->json(
            $this->buildFolderOptions($folders)
        );
    }

    public function getDocumentTypesByOrganization($orgId)
    {
        $documentTypes = Workflow::where('organization_id', $orgId)
            ->get(['id', 'document_type']);

        return response()->json($documentTypes);
    }

    public function getCC(Request $request)
    {
        $organizationId = $request->get('organization_id');
        $divisionId = $request->get('division_id'); 
        $documentTypeId = $request->get('document_type_id');
        
        $users = User::select('users.id', 'users.name', 'users.email', 'user_accesses.organization_id', 'user_accesses.division_id')
                    ->join('user_accesses', 'users.id', '=', 'user_accesses.user_id')
                    ->where('user_accesses.division_id', $divisionId) // 🔥 Filter berdasarkan UserAccess division
                    ->when($organizationId, function($q) use ($organizationId) {
                        return $q->where('user_accesses.organization_id', $organizationId);
                    })
                    ->distinct()
                    ->get();

        return response()->json([
            'success' => true,
            'users' => $users
        ]);
    }

    public function getWorkflowApprovers($workflowId, Request $request)
    {
        $orgId = $request->organization_id;
        $requesterDivisionId = $request->division_id;   // division requester
        $currentUser = auth()->user();

        // Ambil workflow steps
        $workflowSteps = WorkflowStep::with('division')
            ->where('workflow_id', $workflowId)
            ->orderBy('tier')
            ->get();

        $result = [];

        // ================== GROUP 1: Same Division - Higher Role ==================
        $sameDivisionUsers = UserAccess::with(['user', 'role'])
            ->where('organization_id', $orgId)
            ->where('division_id', $requesterDivisionId)
            ->where('user_id', '!=', $currentUser->id)
            ->whereHas('role', function($q) use ($currentUser) {
                // Role lebih tinggi dari user saat ini
                $q->where('role_level', '>', $currentUser->active_role_level ?? 1);
            })
            ->get()
            ->map(function($access) {
                return [
                    'id'         => $access->user->id,
                    'name'       => $access->user->name,
                    'role_name'  => $access->role->role_name ?? '',
                    'role_level' => $access->role->role_level,
                    'source'     => 'same_division'
                ];
            });

        // Masukkan sebagai Tier 0 atau "Direct Superior"
        if ($sameDivisionUsers->isNotEmpty()) {
            $division = Division::find($requesterDivisionId);
            $result[] = [
                'tier'          => 0,
                'title'         => "Tier 0",
                'division_name' => $division->division_name ?? 'Unknown',
                'division_id'   => $division?->id ?? '',
                'sla_days'      => 0,
                'users'         => $sameDivisionUsers,
                'is_same_division' => true
            ];
        }

        // ================== GROUP 2: Workflow Tiers ==================
        foreach ($workflowSteps as $step) {
            $users = UserAccess::with(['user', 'role'])
                ->where('organization_id', $orgId)
                ->where('division_id', $step->division_id)
                ->where('user_id', '!=', $currentUser->id)
                ->whereHas('role', function($q) use ($step) {
                    $q->where('role_level', '>=', $step->min_role_level);
                })
                ->get()
                ->map(function($access) {
                    return [
                        'id'         => $access->user->id,
                        'name'       => $access->user->name,
                        'role_name'  => $access->role->role_name ?? '',
                        'role_level' => $access->role->role_level,
                        'source'     => 'workflow'
                    ];
                });

            $result[] = [
                'tier'          => $step->tier,
                'title'         => "Tier {$step->tier}",
                'division_name' => $step->division?->division_name ?? 'Unknown',
                'division_id'   => $step->division?->id ?? '',
                'sla_days'      => $step->sla_days,
                'users'         => $users,
                'is_same_division' => false
            ];
        }

        return response()->json([
            'success' => true,
            'workflow_steps' => $result
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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
