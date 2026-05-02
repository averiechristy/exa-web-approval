<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoleRequest;
use App\Models\Role;
use App\Services\RoleService;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __construct(private RoleService $roleService)
    {
    }

    public function index(Request $request)
    {

        $perPage = $request->perPage;
        $search = $request->search;
        $role = $this->roleService->getRole($perPage ?? 10, $search);

        return view('role.index',[
            'role' => $role
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
    public function store(RoleRequest $request)
    {
        $this->roleService->createRole($request->validated());

        return redirect()->route('role.index')
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
    public function update(RoleRequest $request, Role $role)
    {
        $this->roleService->updateRole(
            $role,
            $request->validated()
        );

        return redirect()->route('role.index')
            ->with('success', 'Success Update Data');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        $this->roleService->deleteRole($role);

        return redirect()->route('role.index')
            ->with('success', 'Success Delete Data');
    }
}
