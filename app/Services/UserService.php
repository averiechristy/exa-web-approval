<?php

namespace App\Services;

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

        return $query->paginate($perPage)->withQueryString();
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

        return $user;
    }

    public function updateUser(User $user, array $data): User
    {
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

        return $user->fresh(); 
    }

    public function deleteUser(User $user)
    {
        UserAccess::where('user_id', $user->id)->delete();
        return $user->delete();
    }
}
