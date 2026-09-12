<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Jobs\LogActivityJob;
use App\Models\Division;
use App\Models\Organization;
use App\Models\Role;
use App\Models\SystemRole;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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
            'divisions' => Division::with('organization')->orderBy('division_name')->get(),
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
        $user = User::with([
            'userAccesses.organization',
            'userAccesses.division',
            'userAccesses.role',
            'userAccesses.manager',
        ])->findOrFail($id);
        
        return response()->json([
            'id' => $user->id,
            'system_role_id' => $user->system_role_id,
            'name' => $user->name,
            'email' => $user->email,
            'username' => $user->username,
            'organizations' => $user->userAccesses->map(function($org) {
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

public function resetPassword(User $user)
    {
        try {
            $user->update([
                'password' => Hash::make('12345678'),
            ]);

            // Load relasi agar data di log lengkap
            $user->load(['systemRole', 'userAccesses.organization', 'userAccesses.division', 'userAccesses.role', 'userAccesses.manager']);

            // Dispatch Activity Log for Reset Password
            LogActivityJob::dispatchSync(
                logName: 'user',
                causedBy: auth()->user(),
                performedOn: $user,
                event: 'user.reset_password',
                description: 'Reset Password User',
                properties: [
                    'attributes' => [
                        'name' => $user->name,
                        'email' => $user->email,
                        'username' => $user->username,
                        'system_role' => $user->systemRole?->system_role_name,
                        'is_active' => $user->is_active ? 'Active' : 'Inactive',
                        'access' => $user->userAccesses->map(function ($access) {
                            return [
                                'organization' => $access->organization?->organization_name,
                                'division' => $access->division?->division_name,
                                'role' => $access->role?->role_name,
                                'manager' => $access->manager?->name,
                            ];
                        })->values(),
                    ],
                ],
            );

            return redirect()->route('user.index')
                ->with('success', 'Password for user ' . $user->username . ' has been reset to 12345678.');
        } catch (\Exception $e) {
            return redirect()->route('user.index')
                ->with('error', 'Failed to reset password.');
        }
    }

    /**
     * Set user status to inactive
     */

    public function active(User $user)
{
    try {
        $user->update([
            'is_active' => 1,
        ]);

        // Load relasi agar data di log lengkap
        $user->load([
            'systemRole',
            'userAccesses.organization',
            'userAccesses.division',
            'userAccesses.role',
            'userAccesses.manager'
        ]);

        // Dispatch Activity Log for Activate User
        LogActivityJob::dispatchSync(
            logName: 'user',
            causedBy: auth()->user(),
            performedOn: $user,
            event: 'user.activated',
            description: 'Activate User',
            properties: [
                'attributes' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'username' => $user->username,
                    'system_role' => $user->systemRole?->system_role_name,
                    'is_active' => 'Active',
                    'access' => $user->userAccesses->map(function ($access) {
                        return [
                            'organization' => $access->organization?->organization_name,
                            'division' => $access->division?->division_name,
                            'role' => $access->role?->role_name,
                            'manager' => $access->manager?->name,
                        ];
                    })->values(),
                ],
            ],
        );

        return redirect()->route('user.index')
            ->with('success', 'User ' . $user->username . ' has been activated.');

    } catch (\Exception $e) {
        return redirect()->route('user.index')
            ->with('error', 'Failed to activate user.');
    }
}
    public function inactive(User $user)
    {
        // Guard: Prevent self-deactivation
        if (Auth::id() === $user->id) {
            return redirect()->route('user.index')
                ->with('error', 'You cannot deactivate your own account.');
        }

        try {
            $user->update([
                'is_active' => 0,
            ]);

            // Load relasi agar data di log lengkap
            $user->load(['systemRole', 'userAccesses.organization', 'userAccesses.division', 'userAccesses.role', 'userAccesses.manager']);

            // Dispatch Activity Log for Inactive User
            LogActivityJob::dispatchSync(
                logName: 'user',
                causedBy: auth()->user(),
                performedOn: $user,
                event: 'user.inactivated',
                description: 'Inactivate User',
                properties: [
                    'attributes' => [
                        'name' => $user->name,
                        'email' => $user->email,
                        'username' => $user->username,
                        'system_role' => $user->systemRole?->system_role_name,
                        'is_active' => 'Inactive',
                        'access' => $user->userAccesses->map(function ($access) {
                            return [
                                'organization' => $access->organization?->organization_name,
                                'division' => $access->division?->division_name,
                                'role' => $access->role?->role_name,
                                'manager' => $access->manager?->name,
                            ];
                        })->values(),
                    ],
                ],
            );

            return redirect()->route('user.index')
                ->with('success', 'User ' . $user->username . ' has been set to inactive.');
        } catch (\Exception $e) {
            return redirect()->route('user.index')
                ->with('error', 'Failed to deactivate user.');
        }
    }
public function destroy(User $user)
{
    try {
        $this->userService->deleteUser($user);

        return redirect()->route('user.index')
            ->with('success', 'Success Delete Data');
    } catch (\Exception $e) {
        return redirect()->route('user.index')
            ->with('error', $e->getMessage());
    }
}

}
