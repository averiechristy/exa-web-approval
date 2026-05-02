<?php

namespace App\Services;

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

        return $division;
    }

    public function updateDivision(Division $division, array $data): Division
    {
        $division->update($data);
        return $division;
    }

    public function deleteDivision(Division $division)
    {
        return $division->delete();
    }

}
