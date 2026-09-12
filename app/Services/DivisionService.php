<?php

namespace App\Services;

use App\Jobs\LogActivityJob;
use App\Models\Division;

class DivisionService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function getDivision(int $perPage = 10, $search = null)
    {
        $query = Division::query();

        if ($search) {
            $query->whereRaw(
                'LOWER(division_name) LIKE ?',
                ['%' . strtolower($search) . '%']
            );
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function createDivision($data)
    {
        $division = Division::create([
            'division_name' => $data['division_name'],
            'organization_id' => $data['organization_id'],
        ]);

        LogActivityJob::dispatchSync(
            logName: 'division',
            causedBy: auth()->user(),
            performedOn: $division,
            event: 'division.created',
            description: 'Create Division',
            properties: [
                'attributes' => [
                    'division_name' => $division->division_name,
                ],
            ],
        );

        return $division;
    }

    public function updateDivision(Division $division, array $data): Division
    {
        $oldData = [
            'division_name' => $division->division_name,
            'organization_id' => $division->organization_id,
        ];

        $division->update($data);

        LogActivityJob::dispatchSync(
            logName: 'division',
            causedBy: auth()->user(),
            performedOn: $division,
            event: 'division.updated',
            description: 'Update Division',
            properties: [
                'old' => $oldData,
                'attributes' => [
                    'division_name' => $division->division_name,
                    'organization_id' => $division->organization_id,
                ],
            ],
        );

        return $division;
    }

    public function deleteDivision(Division $division)
    {

        if (
            $division->useraccess()->exists() ||
            $division->workflowstep()->exists() ||
            $division->documentapproval()->exists()
        ) {
            throw new \Exception('Division cannot be deleted because it is already used.');
        }

        $oldData = [
            'division_name' => $division->division_name,
            'organization_id' => $division->organization_id,
        ];

        LogActivityJob::dispatchSync(
            logName: 'division',
            causedBy: auth()->user(),
            performedOn: $division,
            event: 'division.deleted',
            description: 'Delete Division',
            properties: [
                'old' => $oldData,
            ],
        );

        return $division->delete();
    }

}
