<?php

namespace App\Services;

use App\Jobs\LogActivityJob;
use App\Models\User;
use App\Models\UserAccess;
use Hash;

class UserService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function getUserDetail($userId)
    {
        return UserAccess::with(['user','organization','division','role','manager'])
                ->where('user_id', $userId)
                ->get()
                ->groupBy('organization_id');
    }

    public function getUser(int $perPage = 10, $search = null)
    {
        $query = User::with([
            'userAccesses.division',
            'userAccesses.organization',
            'userAccesses.role',
            'userAccesses.manager',
        ]);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($search) . '%'])
                ->orWhereRaw('LOWER(email) LIKE ?', ['%' . strtolower($search) . '%']);
            });
        }

        return $query->latest()
                 ->paginate($perPage)
                 ->withQueryString();
    }
    public function createUser($data)
    {
        // 1. Create user
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'username' => $data['username'],
            'system_role_id' => $data['system_role_id'],
            'password' => Hash::make('12345678'),
            'is_active' => true,
        ]);

        // 2. Insert ke user_accesses
        if (!empty($data['organizations'])) {
            foreach ($data['organizations'] as $org) {
                UserAccess::create([
                    'user_id' => $user->id,
                    'organization_id' => $org['organization_id'],
                    'division_id' => $org['division_id'],
                    'role_id' => $org['role_id'],
                    'manager_id' => $org['manager_id'] ?? null,
                ]);
            }
        }

        LogActivityJob::dispatchSync(
            logName: 'user',
            causedBy: auth()->user(),
            performedOn: $user,
            event: 'user.created',
            description: 'Create User',
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

        return $user;
    }

    public function updateUser(User $user, array $data): User
    {
        $user->load([
            'systemRole',
            'userAccesses.organization',
            'userAccesses.division',
            'userAccesses.role',
            'userAccesses.manager',
        ]);

        $oldData = [
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
            })->values()->toArray(),
        ];
        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'username' => $data['username'],
            'system_role_id' => $data['system_role_id'],
            'is_active' => $data['is_active'] ?? $user->is_active,
        ]);

        if (!empty($data['password'])) {
            $user->update([
                'password' => Hash::make($data['password']),
            ]);
        }

        if (isset($data['organizations'])) {
            UserAccess::where('user_id', $user->id)->delete();

            foreach ($data['organizations'] as $org) {
                UserAccess::create([
                    'user_id' => $user->id,
                    'organization_id' => $org['organization_id'],
                    'division_id' => $org['division_id'],
                    'role_id' => $org['role_id'],
                    'manager_id' => $org['manager_id'] ?? null,
                ]);
            }
        }

        $user = $user->fresh()->load([
            'systemRole',
            'userAccesses.organization',
            'userAccesses.division',
            'userAccesses.role',
            'userAccesses.manager',
        ]);

        $newData = [
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
            })->values()->toArray(),
        ];

        LogActivityJob::dispatchSync(
            logName: 'user',
            causedBy: auth()->user(),
            performedOn: $user,
            event: 'user.updated',
            description: 'Update User',
            properties: [
                'old' => $oldData,
                'attributes' => $newData,
            ],
        );

        return $user;
    }

    public function deleteUser(User $user)
    {
        if (auth()->id() === $user->id) {
            throw new \Exception('You cannot delete your own account.');
        }

        // Tidak boleh menghapus Superadmin terakhir
        if ($user->isSuperadmin()) {
            $superadminCount = User::where('system_role_id', 1)
                ->whereNull('deleted_at')
                ->count();

            if ($superadminCount <= 1) {
                throw new \Exception('The last Superadmin cannot be deleted.');
            }
        }

        // Tidak boleh dihapus jika masih digunakan
        if (
            $user->userAccesses()->exists() ||
            $user->documentApprovals()->exists()
        ) {
            throw new \Exception('User cannot be deleted because it is already used.');
        }

        $user->load([
            'systemRole',
            'userAccesses.organization',
            'userAccesses.division',
            'userAccesses.role',
            'userAccesses.manager',
        ]);

        $oldData = [
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
            })->values()->toArray(),
        ];

        LogActivityJob::dispatchSync(
            logName: 'user',
            causedBy: auth()->user(),
            performedOn: $user,
            event: 'user.deleted',
            description: 'Delete User',
            properties: [
                'old' => $oldData,
            ],
        );

        UserAccess::where('user_id', $user->id)->delete();

        return $user->delete();
    }
}
