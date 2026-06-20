<?php

namespace App\Services;

use App\Jobs\LogActivityJob;
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

        LogActivityJob::dispatchSync(
            logName: 'organization',
            causedBy: auth()->user(),
            performedOn: $organization,
            event: 'organization.created',
            description: 'Create Organization',
            properties: [
                'attributes' => [
                    'organization_name' => $organization->organization_name,
                ],
            ],
        );

        return $organization;
    }

    public function updateOrganization(Organization $organization, array $data): Organization
    {
        $oldData = [
            'organization_name' => $organization->organization_name,
        ];

        $organization->update($data);

        LogActivityJob::dispatchSync(
            logName: 'organization',
            causedBy: auth()->user(),
            performedOn: $organization,
            event: 'organization.updated',
            description: 'Update Organization',
            properties: [
                'old' => $oldData,
                'attributes' => [
                    'organization_name' => $organization->organization_name,
                ],
            ],
        );
        return $organization;
    }

    public function deleteOrganization(Organization $organization)
    {
        $oldData = [
            'organization_name' => $organization->organization_name,
        ];

        LogActivityJob::dispatchSync(
            logName: 'organization',
            causedBy: auth()->user(),
            performedOn: $organization,
            event: 'organization.deleted',
            description: 'Delete Organization',
            properties: [
                'old' => $oldData,
            ],
        );

        return $organization->delete();
    }
}
