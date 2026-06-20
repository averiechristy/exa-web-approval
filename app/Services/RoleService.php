<?php

namespace App\Services;

use App\Jobs\LogActivityJob;
use App\Models\Role;

class RoleService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function getRole(int $perPage = 10, $search = null)
    {
        $query = Role::query();

        if ($search) {
            $query->whereRaw(
                'LOWER(role_name) LIKE ?',
                ['%' . strtolower($search) . '%']
            );
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function createRole($data)
    {
        $role = Role::create([
            'role_name' => $data['role_name'],
            'role_level' => $data['role_level']
        ]);

        LogActivityJob::dispatchSync(
            logName: 'role',
            causedBy: auth()->user(),
            performedOn: $role,
            event: 'role.created',
            description: 'Create Role',
            properties: [
                'attributes' => [
                    'role_name' => $role->role_name,
                    'role_level' => $role->role_level
                ],
            ],
        );

        return $role;
    }

    public function updateRole(Role $role, array $data): Role
    {
        $oldData = [
            'role_name' => $role->role_name,
            'role_level' => $role->role_level
        ];

        $role->update($data);

         LogActivityJob::dispatchSync(
            logName: 'role',
            causedBy: auth()->user(),
            performedOn: $role,
            event: 'role.updated',
            description: 'Update Role',
            properties: [
                'old' => $oldData,
                'attributes' => [
                    'role_name' => $role->role_name,
                    'role_level' => $role->role_level
                ],
            ],
        );

        return $role;
    }

    public function deleteRole(Role $role)
    {
        $oldData = [
            'role_name' => $role->role_name,
            'role_level' => $role->role_level
        ];

        LogActivityJob::dispatchSync(
            logName: 'role',
            causedBy: auth()->user(),
            performedOn: $role,
            event: 'role.deleted',
            description: 'Delete Role',
            properties: [
                'old' => $oldData,
            ],
        );

        return $role->delete();
    }
}
