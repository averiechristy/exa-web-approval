<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\Division;
use App\Models\Organization;
use App\Models\Role;
use App\Models\SystemRole;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function __construct(private UserService $userService)
    {
    }

    public function getData($id)
    {
        $data = $this->userService->getUserDetail($id);
        return response()->json($data);
    }

    public function index(Request $request)
    {
        $perPage = $request->perPage;
        $search = $request->search;
        $user = $this->userService->getUser($perPage ?? 10, $search);

        return view('user.index',[
            'user' => $user,
            'organizations' => Organization::all(),
            'divisions' => Division::all(),
            'roles' => Role::all(),
            'systemRoles' => SystemRole::all(),
        ]);
    }
    public function getManagers(Request $request)
    {
        $divisionId = $request->division_id;
        $roleId = $request->role_id;
        $organizationId = $request->organization_id;

        // ambil role sekarang
        $role = Role::find($roleId);

        if (!$role) {
            return response()->json([]);
        }

        // cari parent role (order lebih kecil, paling dekat)
        $parentRole = Role::where('role_level', '>', $role->role_level)
            ->orderBy('role_level', 'desc')
            ->first();

        // kalau gak ada parent (top level)
        if (!$parentRole) {
            return response()->json([]);
        }

        // ambil user yang sesuai di user_accesses
        $managers = User::whereHas('userAccesses', function ($q) use ($divisionId, $parentRole, $organizationId) {
            $q->where('division_id', $divisionId)
            ->where('role_id', $parentRole->id);

            // kalau ada organization_id, filter juga
            if ($organizationId) {
                $q->where('organization_id', $organizationId);
            }
        })
        ->select('id', 'name') // biar ringan buat dropdown
        ->get();

        return response()->json($managers);
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
    public function store(UserRequest $request)
    {
        $this->userService->createUser($request->validated());

        return redirect()->route('user.index')
            ->with('success', 'Success Add Data');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        // Load dengan manager relation
        $user->load([
            'userAccesses.organization', 
            'userAccesses.division', 
            'userAccesses.role',
            'userAccesses.manager:id,name' // ✅ Load manager data
        ]);

        $organizations = $user->userAccesses->map(function ($access) {
            return [
                'organization_id' => $access->organization_id,
                'division_id' => $access->division_id,
                'role_id' => $access->role_id,
                'manager_id' => $access->manager_id,
                'manager_name' => $access->manager?->name ?? null, // ✅ Safe access
            ];
        });

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'username' => $user->username,
            'system_role_id' => $user->system_role_id,
            'organizations' => $organizations,
        ]);
    }
        /**
         * Show the form for editing the specified resource.
         */
    // Di Controller
    public function edit($id) {
        $user = User::with(['organizations.manager'])->findOrFail($id);
        
        return response()->json([
            'id' => $user->id,
            'system_role_id' => $user->system_role_id,
            'name' => $user->name,
            'email' => $user->email,
            'username' => $user->username,
            'organizations' => $user->organizations->map(function($org) {
                return [
                    'organization_id' => $org->organization_id,
                    'division_id' => $org->division_id,
                    'role_id' => $org->role_id,
                    'manager_id' => $org->manager_id,  // ✅ Pastikan ini ada
                    'manager_name' => $org->manager->name ?? null
                ];
            })
        ]);
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(UserRequest $request, User $user)
    {
        $this->userService->updateUser(
            $user,
            $request->validated()
        );

        return redirect()->route('user.index')
            ->with('success', 'Success Update Data');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $this->userService->deleteUser($user);

        return redirect()->route('user.index')
            ->with('success', 'Success Delete Data');
    }

}
