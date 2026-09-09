<?php

use App\Models\Division;
use App\Models\DocumentApproval;
use App\Models\Documents;
use App\Models\Folder;
use App\Models\Organization;
use App\Models\SystemRole;
use App\Models\User;
use App\Models\Workflow;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('shows addressee names and supports addressee filtering in inbox', function () {
    $organization = Organization::create([
        'organization_name' => 'Test Org',
    ]);

    $division = Division::create([
        'division_name' => 'Operations',
    ]);

    $systemRole = SystemRole::create([
        'system_role_name' => 'User',
    ]);

    $requester = User::factory()->create([
        'name' => 'Requester Person',
        'username' => 'requester',
        'system_role_id' => $systemRole->id,
    ]);

    $addressee = User::factory()->create([
        'name' => 'Addressee Person',
        'username' => 'addressee',
        'system_role_id' => $systemRole->id,
    ]);

    $folder = Folder::create([
        'organization_id' => $organization->id,
        'folder_name' => 'Inbox Folder',
    ]);

    $workflow = Workflow::create([
        'organization_id' => $organization->id,
        'document_type' => 'General',
    ]);

    $document = Documents::create([
        'organization_id' => $organization->id,
        'folder_id' => $folder->id,
        'document_name' => 'Test Document',
        'path' => 'documents/test.pdf',
        'status' => 'Need Approval',
        'requester_id' => $requester->id,
        'requester_division_id' => $division->id,
        'workflow_id' => $workflow->id,
        'current_tier' => 1,
    ]);

    DocumentApproval::create([
        'document_id' => $document->id,
        'division_id' => $division->id,
        'approver_id' => $requester->id,
        'approver_order' => 1,
        'show_on_doc' => true,
        'status' => 'Pending',
        'remarks' => '',
        'sla_days' => 3,
        'started_at' => now(),
        'due_at' => now()->addDays(2),
        'completed_at' => null,
        'is_overdue' => false,
        'tier' => 1,
        'workflow_step_id' => 1,
        'is_requester' => true,
    ]);

    DocumentApproval::create([
        'document_id' => $document->id,
        'division_id' => $division->id,
        'approver_id' => $addressee->id,
        'approver_order' => 2,
        'show_on_doc' => true,
        'status' => 'Pending',
        'remarks' => '',
        'sla_days' => 3,
        'started_at' => now(),
        'due_at' => now()->addDays(2),
        'completed_at' => null,
        'is_overdue' => false,
        'tier' => 1,
        'workflow_step_id' => 1,
        'is_requester' => false,
    ]);

    $response = $this
        ->withSession(['active_organization_id' => $organization->id])
        ->withoutMiddleware()
        ->actingAs($requester)
        ->get('/inbox?addressee_id=' . $addressee->id);

    $response->assertOk();
    $response->assertSee('Addressee');
    $response->assertSee('Addressee Person');
    $response->assertSee('All Addressees');
});
