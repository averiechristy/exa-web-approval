<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\Folder;
use App\Models\Organization;
use App\Models\User;
use App\Models\Workflow;
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

    public function getApprovers(Request $request)
    {
        $organizationId = $request->get('organization_id');
        $divisionId = $request->get('division_id'); 
        $documentTypeId = $request->get('document_type_id');
        
        $currentUser = auth()->user();
        $minRoleLevel = 1; // Default untuk superadmin
        
        // Cek role active dari session atau current user
        if ($currentUser && $currentUser->active_role_id) {
            $activeRole = \App\Models\Role::find($currentUser->active_role_id);
            $minRoleLevel = $activeRole ? $activeRole->role_level : 1;
        }
        
        // Ambil semua division_id yang relevan berdasarkan workflow tiers
        $relevantDivisionIds = collect();
        
        if ($organizationId && $documentTypeId) {
            // Ambil workflow steps untuk document type ini
            $workflowSteps = \App\Models\WorkflowStep::select('division_id', 'tier', 'min_role_level')
                ->join('workflows', 'workflow_steps.workflow_id', '=', 'workflows.id')
                ->where('workflows.organization_id', $organizationId)
                ->where('workflows.id', $documentTypeId)
                ->orderBy('tier')
                ->get();
            
            // Kumpulkan division_id yang min_role_levelnya >= current user role level
            foreach ($workflowSteps as $step) {
                if ($step->min_role_level >= $minRoleLevel) {
                    $relevantDivisionIds->push($step->division_id);
                }
            }
            
            // Tambahkan division_id saat ini jika belum ada
            if (!$relevantDivisionIds->contains($divisionId)) {
                $relevantDivisionIds->push($divisionId);
            }
        } else {
            // Fallback ke division_id saat ini saja
            $relevantDivisionIds->push($divisionId);
        }
        
        $users = User::select(
                'users.id', 
                'users.name', 
                'users.email', 
                'user_accesses.organization_id', 
                'user_accesses.division_id',
                'roles.role_name as role_name',
                'roles.role_level'
            )
            ->join('user_accesses', 'users.id', '=', 'user_accesses.user_id')
            ->join('roles', 'user_accesses.role_id', '=', 'roles.id')
            ->whereIn('user_accesses.division_id', $relevantDivisionIds)
            ->where('roles.role_level', '>', $minRoleLevel) // Hanya user dengan role_level lebih tinggi dari current user
            ->when($organizationId, function($q) use ($organizationId) {
                return $q->where('user_accesses.organization_id', $organizationId);
            })
            ->distinct()
            ->get();

        return response()->json([
            'success' => true,
            'users' => $users,
            'current_role_level' => $minRoleLevel,
            'relevant_divisions' => $relevantDivisionIds, // Optional: untuk debug
            'workflow_steps_found' => $workflowSteps ?? collect() // Optional: untuk debug
        ]);
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
