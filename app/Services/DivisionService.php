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
            'division_name' => $data['division_name']
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
                ],
            ],
        );

        return $division;
    }

    public function deleteDivision(Division $division)
    {
        $oldData = [
            'division_name' => $division->division_name,
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
