<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrganizationRequest;
use App\Http\Resources\OrganizationResource;
use App\Models\Organization;
use App\Services\OrganizationService;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function __construct(private OrganizationService $organizationService)
    {
    }

    public function index(Request $request)
    {

        $perPage = $request->perPage;
        $search = $request->search;
        $organization = $this->organizationService->getOrganization($perPage ?? 10, $search);

        return view('organization.index',[
            'organization' => $organization
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(OrganizationRequest $request)
    {
        $this->organizationService->createOrganization($request->validated());

        return redirect()->route('organization.index')
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
    public function update(OrganizationRequest $request, Organization $organization)
    {
        $this->organizationService->updateOrganization(
            $organization,
            $request->validated()
        );

        return redirect()->route('organization.index')
            ->with('success', 'Success Update Data');
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Organization $organization)
    {
        $this->organizationService->deleteOrganization($organization);

        return redirect()->route('organization.index')
            ->with('success', 'Success Delete Data');
    }
}
