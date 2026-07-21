<?php

namespace Database\Seeders;

use App\Models\Division;
use App\Models\DocumentApproval;
use App\Models\Documents;
use App\Models\Folder;
use App\Models\Organization;
use App\Models\Role;
use App\Models\SystemRole;
use App\Models\User;
use App\Models\UserAccess;
use App\Models\Workflow;
use App\Models\WorkflowStep;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SlaDashboardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $organization = Organization::updateOrCreate(
            ['organization_name' => 'SLA Demo Organization'],
            ['organization_name' => 'SLA Demo Organization']
        );

        $finance = Division::updateOrCreate(
            ['division_name' => 'Finance'],
            ['division_name' => 'Finance']
        );
        $it = Division::updateOrCreate(
            ['division_name' => 'IT'],
            ['division_name' => 'IT']
        );
        $hr = Division::updateOrCreate(
            ['division_name' => 'HR'],
            ['division_name' => 'HR']
        );

        $staffRole = Role::updateOrCreate(
            ['role_name' => 'Staff'],
            ['role_level' => 1, 'role_name' => 'Staff']
        );
        $supervisorRole = Role::updateOrCreate(
            ['role_name' => 'Supervisor'],
            ['role_level' => 2, 'role_name' => 'Supervisor']
        );
        $managerRole = Role::updateOrCreate(
            ['role_name' => 'Manager'],
            ['role_level' => 3, 'role_name' => 'Manager']
        );

        $superAdminRoleId = SystemRole::where('system_role_name', 'Superadmin')->value('id') ?? 1;

        User::updateOrCreate(
            ['email' => 'superadmin@gmail.com'],
            [
                'name' => 'Super Admin',
                'username' => 'superadmin',
                'system_role_id' => $superAdminRoleId,
                'password' => Hash::make('12345678'),
                'is_active' => true,
            ]
        );

        $manager = User::updateOrCreate(
            ['email' => 'sla.manager@example.com'],
            [
                'name' => 'SLA Manager',
                'username' => 'sla-manager',
                'system_role_id' => 2,
                'password' => Hash::make('password123'),
                'is_active' => true,
            ]
        );

        $reviewer = User::updateOrCreate(
            ['email' => 'sla.reviewer@example.com'],
            [
                'name' => 'SLA Reviewer',
                'username' => 'sla-reviewer',
                'system_role_id' => 2,
                'password' => Hash::make('password123'),
                'is_active' => true,
            ]
        );

        $requester = User::updateOrCreate(
            ['email' => 'sla.requester@example.com'],
            [
                'name' => 'SLA Requester',
                'username' => 'sla-requester',
                'system_role_id' => 2,
                'password' => Hash::make('password123'),
                'is_active' => true,
            ]
        );

        $staff = User::updateOrCreate(
            ['email' => 'sla.staff@example.com'],
            [
                'name' => 'SLA Staff',
                'username' => 'sla-staff',
                'system_role_id' => 2,
                'password' => Hash::make('password123'),
                'is_active' => true,
            ]
        );

        UserAccess::updateOrCreate(
            ['user_id' => $manager->id, 'organization_id' => $organization->id],
            ['division_id' => $it->id, 'role_id' => $managerRole->id, 'manager_id' => null]
        );
        UserAccess::updateOrCreate(
            ['user_id' => $reviewer->id, 'organization_id' => $organization->id],
            ['division_id' => $finance->id, 'role_id' => $supervisorRole->id, 'manager_id' => $manager->id]
        );
        UserAccess::updateOrCreate(
            ['user_id' => $requester->id, 'organization_id' => $organization->id],
            ['division_id' => $hr->id, 'role_id' => $staffRole->id, 'manager_id' => $manager->id]
        );
        UserAccess::updateOrCreate(
            ['user_id' => $staff->id, 'organization_id' => $organization->id],
            ['division_id' => $finance->id, 'role_id' => $staffRole->id, 'manager_id' => $reviewer->id]
        );

        $folder = Folder::updateOrCreate(
            ['organization_id' => $organization->id, 'folder_name' => 'SLA Demo Folder'],
            ['parent_id' => null]
        );

        $workflow = Workflow::updateOrCreate(
            ['organization_id' => $organization->id, 'document_type' => 'Purchase Request'],
            ['document_type' => 'Purchase Request']
        );

        $stepOne = WorkflowStep::updateOrCreate(
            ['workflow_id' => $workflow->id, 'tier' => 1],
            ['division_id' => $it->id, 'sla_days' => 2, 'min_role_level' => 1]
        );
        $stepTwo = WorkflowStep::updateOrCreate(
            ['workflow_id' => $workflow->id, 'tier' => 2],
            ['division_id' => $finance->id, 'sla_days' => 1, 'min_role_level' => 2]
        );

        $documentsData = [
            [
                'name' => 'Laptop Request',
                'status' => 'Pending',
                'approval_status' => 'Pending',
                'remarks' => 'Waiting for manager approval',
                'sla_days' => 2,
                'started_days_ago' => 6,
                'due_days_ago' => 3,
                'completed_days_ago' => null,
                'is_overdue' => true,
                'created_days_ago' => 6,
                'updated_days_ago' => 1,
                'division_id' => $it->id,
                'step_id' => $stepOne->id,
                'second_approver' => true,
                'requester_id' => $requester->id,
            ],
            [
                'name' => 'Travel Reimbursement',
                'status' => 'Pending',
                'approval_status' => 'Pending',
                'remarks' => 'Pending review for finance',
                'sla_days' => 3,
                'started_days_ago' => 3,
                'due_days_ago' => 1,
                'completed_days_ago' => null,
                'is_overdue' => false,
                'created_days_ago' => 3,
                'updated_days_ago' => 1,
                'division_id' => $finance->id,
                'step_id' => $stepTwo->id,
                'second_approver' => false,
                'requester_id' => $requester->id,
            ],
            [
                'name' => 'Office Supplies',
                'status' => 'Approved',
                'approval_status' => 'Approved',
                'remarks' => 'Approved on time',
                'sla_days' => 2,
                'started_days_ago' => 4,
                'due_days_ago' => 2,
                'completed_days_ago' => 1,
                'is_overdue' => false,
                'created_days_ago' => 4,
                'updated_days_ago' => 1,
                'division_id' => $it->id,
                'step_id' => $stepOne->id,
                'second_approver' => false,
                'requester_id' => $staff->id,
            ],
            [
                'name' => 'Vendor Contract',
                'status' => 'Approved',
                'approval_status' => 'Approved',
                'remarks' => 'Approved after SLA breach',
                'sla_days' => 1,
                'started_days_ago' => 5,
                'due_days_ago' => 2,
                'completed_days_ago' => 2,
                'is_overdue' => true,
                'created_days_ago' => 5,
                'updated_days_ago' => 2,
                'division_id' => $finance->id,
                'step_id' => $stepTwo->id,
                'second_approver' => false,
                'requester_id' => $requester->id,
            ],
            [
                'name' => 'Training Request',
                'status' => 'Rejected',
                'approval_status' => 'Rejected',
                'remarks' => 'Rejected due to missing budget',
                'sla_days' => 2,
                'started_days_ago' => 7,
                'due_days_ago' => 5,
                'completed_days_ago' => 4,
                'is_overdue' => false,
                'created_days_ago' => 7,
                'updated_days_ago' => 4,
                'division_id' => $hr->id,
                'step_id' => $stepOne->id,
                'second_approver' => false,
                'requester_id' => $requester->id,
            ],
            [
                'name' => 'Software License Renewal',
                'status' => 'Pending',
                'approval_status' => 'Pending',
                'remarks' => 'Needs urgent approval',
                'sla_days' => 1,
                'started_days_ago' => 8,
                'due_days_ago' => 5,
                'completed_days_ago' => null,
                'is_overdue' => true,
                'created_days_ago' => 8,
                'updated_days_ago' => 2,
                'division_id' => $it->id,
                'step_id' => $stepOne->id,
                'second_approver' => true,
                'requester_id' => $staff->id,
            ],
        ];

        foreach ($documentsData as $data) {
            $createdAt = Carbon::now()->subDays($data['created_days_ago']);
            $updatedAt = Carbon::now()->subDays($data['updated_days_ago']);
            $startedAt = Carbon::now()->subDays($data['started_days_ago']);
            $dueAt = Carbon::now()->subDays($data['due_days_ago']);
            $completedAt = $data['completed_days_ago'] === null
                ? Carbon::now()->subDay()
                : Carbon::now()->subDays($data['completed_days_ago']);

            $document = Documents::create([
                'organization_id' => $organization->id,
                'folder_id' => $folder->id,
                'document_name' => $data['name'],
                'path' => 'storage/demo/' . strtolower(str_replace(' ', '-', $data['name'])) . '.pdf',
                'status' => $data['status'],
                'requester_id' => $data['requester_id'],
                'requester_division_id' => $data['division_id'],
                'workflow_id' => $workflow->id,
                'current_tier' => 1,
                'created_at' => $createdAt,
                'updated_at' => $updatedAt,
            ]);

            DocumentApproval::create([
                'document_id' => $document->id,
                'division_id' => $data['division_id'],
                'approver_id' => $manager->id,
                'approver_order' => 1,
                'show_on_doc' => true,
                'status' => $data['approval_status'],
                'remarks' => $data['remarks'],
                'sla_days' => $data['sla_days'],
                'started_at' => $startedAt,
                'due_at' => $dueAt,
                'completed_at' => $completedAt,
                'is_overdue' => $data['is_overdue'],
                'tier' => 1,
                'workflow_step_id' => $data['step_id'],
                'created_at' => $createdAt,
                'updated_at' => $updatedAt,
            ]);

            if ($data['second_approver']) {
                DocumentApproval::create([
                    'document_id' => $document->id,
                    'division_id' => $finance->id,
                    'approver_id' => $reviewer->id,
                    'approver_order' => 2,
                    'show_on_doc' => true,
                    'status' => 'Pending',
                    'remarks' => 'Secondary pending approval',
                    'sla_days' => 1,
                    'started_at' => $startedAt,
                    'due_at' => $dueAt,
                    'completed_at' => $completedAt,
                    'is_overdue' => $data['is_overdue'],
                    'tier' => 2,
                    'workflow_step_id' => $stepTwo->id,
                    'created_at' => $createdAt,
                    'updated_at' => $updatedAt,
                ]);
            }
        }

        $this->command->info('SLA dashboard dummy data seeded successfully.');
    }
}
