<?php

namespace App\Services;

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

        return $role;
    }

    public function updateRole(Role $role, array $data): Role
    {
        $role->update($data);
        return $role;
    }

    public function deleteRole(Role $role)
    {
        return $role->delete();
    }
}
