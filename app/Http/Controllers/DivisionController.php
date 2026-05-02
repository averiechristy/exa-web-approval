<?php

namespace App\Http\Controllers;

use App\Http\Requests\DivisionRequest;
use App\Models\Division;
use App\Services\DivisionService;
use Illuminate\Http\Request;

class DivisionController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function __construct(private DivisionService $divisionService)
    {
    }
    public function index(Request $request)
    {
        $perPage = $request->perPage;
        $search = $request->search;
        $division = $this->divisionService->getDivision($perPage ?? 10, $search);

        return view('division.index',[
            'division' => $division
        ]);
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
    public function store(DivisionRequest $request)
    {
        $this->divisionService->createDivision($request->validated());

        return redirect()->route('division.index')
            ->with('success', 'Success Add Data');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DivisionRequest $request, Division $division)
    {
        $this->divisionService->updateDivision(
            $division,
            $request->validated()
        );

        return redirect()->route('division.index')
            ->with('success', 'Success Update Data');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Division $division)
    {
        $this->divisionService->deleteDivision($division);

        return redirect()->route('division.index')
            ->with('success', 'Success Delete Data');
    }
}
