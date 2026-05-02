<?php

namespace App\Services;

use App\Models\Organization;

class OrganizationService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function getOrganization(int $perPage = 10, $search = null)
    {
        $query = Organization::query();

        if ($search) {
            $query->whereRaw(
                'LOWER(organization_name) LIKE ?',
                ['%' . strtolower($search) . '%']
            );
        }
        return $query->paginate($perPage)->withQueryString();
    }

    public function createOrganization($data)
    {
        $organization = Organization::create([
            'organization_name' => $data['organization_name']
        ]);

        return $organization;
    }

    public function updateOrganization(Organization $organization, array $data): Organization
    {
        $organization->update($data);
        return $organization;
    }

    public function deleteOrganization(Organization $organization)
    {
        return $organization->delete();
    }
}
