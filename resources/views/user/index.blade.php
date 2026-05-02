@extends('layouts.app')

@section('title', 'User')

@section('content')
<div class="container-fluid">
    @include('components.alert')

    <h1 class="h4 mb-4 text-gray-800">User</h1>

    <div class="card shadow mb-4">
        <div class="card-body">
            {{-- HEADER: Search & Add Button --}}
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h6 class="font-weight-bold text-primary mb-2">User List</h6>
                    <input 
                        type="text" 
                        id="searchInput"
                        name="search"
                        class="form-control"
                        placeholder="Search User (by name & email)"
                        value="{{ request('search') }}"
                        style="width: 250px;"
                    >
                </div>
                <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addModal">
                    <i class="fas fa-plus"></i> Add User
                </button>
            </div>

            {{-- TABLE SECTION --}}
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="bg-light">
                        <tr>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Detail</th>
                            <th width="150" class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($user as $item)
                            <tr>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->username }}</td>
                                <td>{{ $item->email }}</td>
                                <td>
                                    <button type="button" class="btn btn-link btn-detail" data-id="{{ $item->id }}">
                                        See Detail
                                    </button>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-light edit-btn" data-id="{{ $item->id }}">
                                        <i class="fas fa-edit text-primary"></i>
                                    </button>
                                    <button 
                                        class="btn btn-sm btn-light deleteBtn" 
                                        data-id="{{ $item->id }}"
                                        data-username="{{ $item->username }}"
                                        data-toggle="modal" 
                                        data-target="#deleteModal"
                                    >
                                        <i class="fas fa-trash text-danger"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">No data available</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- FOOTER: Pagination & PerPage --}}
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="d-flex align-items-center">
                    <span class="mr-2 text-muted">Show</span>
                    <form method="GET" id="perPageForm" class="mb-0">
                        <select 
                            name="perPage" 
                            onchange="this.form.submit()" 
                            class="custom-select custom-select-sm rounded-pill px-3"
                            style="width: 80px;"
                        >
                            <option value="10" @selected(request('perPage') == 10)>10</option>
                            <option value="25" @selected(request('perPage') == 25)>25</option>
                            <option value="50" @selected(request('perPage') == 50)>50</option>
                        </select>
                    </form>
                    <span class="ml-2 text-muted">entries</span>
                </div>

                <div>
                    @if ($user->lastPage() > 1)
                       {{ $user->onEachSide(2)->links('pagination::bootstrap-4') }}
                    @else
                        <ul class="pagination">
                            <li class="page-item disabled"><span class="page-link">«</span></li>
                            <li class="page-item active"><span class="page-link">1</span></li>
                            <li class="page-item disabled"><span class="page-link">»</span></li>
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL CREATE --}}
<div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="POST" action="{{ route('user.store') }}" id="addUserForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Create User</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    {{-- SYSTEM ROLE --}}
                    <div class="form-group">
                        <label class="font-weight-bold">System Role</label>
                        <select name="system_role_id" id="systemRoleSelect" class="form-control">
                            <option value="">Select Role</option>
                            @foreach($systemRoles as $role)
                                <option value="{{ $role->id }}">{{ $role->system_role_name }}</option>
                            @endforeach
                        </select>
                        <small class="text-danger"></small> 
                    </div>
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control">
                        <small class="text-danger"></small>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control">
                        <small class="text-danger"></small>
                    </div>
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control">
                        <small class="text-danger"></small>
                    </div>

                    <hr>

                    {{-- ORGANIZATION SECTION --}}
                    <div id="organizationSection">
                        <div class="d-flex justify-content-between mb-3">
                            <h6 class="font-weight-bold text-primary">Organization & Role</h6>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="addOrgRow">
                                <i class="fas fa-plus"></i> Add Row
                            </button>
                        </div>

                        <div id="orgContainer">
                            <div class="row org-row mb-3 border-bottom pb-3">
                                <div class="col-md-3">
                                    <label class="small">Organization</label>
                                    <select name="organizations[0][organization_id]" class="form-control org-select">
                                        <option value="">Select</option>
                                        @foreach($organizations as $org)
                                            <option value="{{ $org->id }}">{{ $org->organization_name }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-danger"></small>
                                </div>
                                <div class="col-md-2">
                                    <label class="small">Division</label>
                                    <select name="organizations[0][division_id]" class="form-control division-select">
                                        <option value="">Select</option>
                                        @foreach($divisions as $div)
                                            <option value="{{ $div->id }}">{{ $div->division_name }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-danger"></small>
                                </div>
                                <div class="col-md-2">
                                    <label class="small">Role</label>
                                    <select name="organizations[0][role_id]" class="form-control role-select">
                                        <option value="">Select</option>
                                        @foreach($roles as $role)
                                            <option value="{{ $role->id }}">{{ $role->role_name }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-danger"></small>
                                </div>
                                <div class="col-md-3">
                                    <label class="small">Manager</label>
                                    <select name="organizations[0][manager_id]" class="form-control manager-select">
                                        <option value="">Select Manager</option>
                                    </select>
                                    <small class="text-danger"></small>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="button" class="btn btn-danger btn-block removeOrg">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-clear-form">Clear</button>
                    <button type="submit" class="btn btn-primary px-4">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL EDIT --}}
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="POST" action="" id="editUserForm">
                @csrf @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit User</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    {{-- HIDDEN USER ID --}}
                    <input type="hidden" name="id" id="editUserId">
                    
                    {{-- SYSTEM ROLE --}}
                    <div class="form-group">
                        <label class="font-weight-bold">System Role</label>
                        <select name="system_role_id" id="editSystemRoleSelect" class="form-control">
                            <option value="">Select Role</option>
                            @foreach($systemRoles as $role)
                                <option value="{{ $role->id }}">{{ $role->system_role_name }}</option>
                            @endforeach
                        </select>
                        <small class="text-danger"></small> 
                    </div>
                    
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="name" id="editName" class="form-control">
                        <small class="text-danger"></small>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" id="editEmail" class="form-control">
                        <small class="text-danger"></small>
                    </div>
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" id="editUsername" class="form-control">
                        <small class="text-danger"></small>
                    </div>

                    <hr>

                    {{-- ORGANIZATION SECTION --}}
                    <div id="editOrganizationSection">
                        <div class="d-flex justify-content-between mb-3">
                            <h6 class="font-weight-bold text-primary">Organization & Role</h6>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="editAddOrgRow">
                                <i class="fas fa-plus"></i> Add Row
                            </button>
                        </div>

                        <div id="editOrgContainer">
                            <!-- Dynamic rows will be populated here -->
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-clear-edit-form">Clear</button>
                    <button type="submit" class="btn btn-primary px-4">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDetail" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">User Organization Info</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body">

        <!-- basic info -->
        <div class="mb-3">
          <p><b>Name:</b> <span id="name"></span></p>
        </div>

        <hr>

        <!-- dynamic organization list -->
        <div id="orgList"></div>

      </div>

    </div>
  </div>
</div>

<!-- Delete Form Hidden-->
 <form id="globalDeleteForm" method="POST" style="display:none;">
    @csrf
    @method('DELETE')
</form>

@endsection

@push('scripts')
<script>
$(document).on("click", ".btn-detail", function () {
    let id = $(this).data("id");

    $.ajax({
        url: "/get-data/" + id,
        type: "GET",
        success: function (response) {

            // =========================
            // HANDLE EMPTY RESPONSE
            // =========================
            if (!response || Object.keys(response).length === 0) {
                $("#modalDetail #name").text("-");
                $("#modalDetail #orgList").html("<div class='text-muted'>No organization data</div>");

                $("#modalDetail").modal("show");
                return;
            }

            // =========================
            // SAFE GET FIRST ITEM
            // =========================
            let firstKey = Object.keys(response)[0];
            let firstItem = response[firstKey]?.[0];

            // kalau masih kosong juga
            if (!firstItem) {
                $("#modalDetail #name").text("-");
                $("#modalDetail #email").text("-");
                $("#modalDetail #orgList").html("<div class='text-muted'>No organization data</div>");
                $("#modalDetail").modal("show");
                return;
            }

            // =========================
            // USER INFO
            // =========================
            $("#modalDetail #name").text(firstItem.user?.name ?? '-');
            $("#modalDetail #email").text(firstItem.user?.email ?? '-');

            // =========================
            // ORGANIZATION RENDER
            // =========================
            let html = "";

            Object.keys(response).forEach(function (orgId) {
                let items = response[orgId];

                if (!items || items.length === 0) return;

                let orgName = items[0]?.organization?.organization_name ?? '-';

                html += `
                    <div class="mb-3 p-2 border rounded">
                        <h6><b>${orgName}</b></h6>
                `;

                items.forEach(function (item) {
                    html += `
                        <div class="ms-2 mb-2">
                            <div><b>Role:</b> ${item.role?.role_name ?? '-'}</div>
                            <div><b>Division:</b> ${item.division?.division_name ?? '-'}</div>
                            <div><b>Manager:</b> ${item.manager?.name ?? '-'}</div>
                        </div>
                        <hr>
                    `;
                });

                html += `</div>`;
            });

            $("#modalDetail #orgList").html(html || "<div class='text-muted'>No organization data</div>");

            $("#modalDetail").modal("show");
        },

        error: function (err) {
            console.log(err);

            $("#modalDetail #name").text("-");
            $("#modalDetail #email").text("-");
            $("#modalDetail #orgList").html("<div class='text-danger'>Failed to load data</div>");

            $("#modalDetail").modal("show");
        }
    });
});

    // Organization Options
    const organizationOptions = `
        @foreach($organizations as $org)
        <option value="{{ $org->id }}">{{ $org->organization_name }}</option>
        @endforeach
    `;
    
    // Division Options  
    const divisionOptions = `
        @foreach($divisions as $div)
        <option value="{{ $div->id }}">{{ $div->division_name }}</option>
        @endforeach
    `;
    
    // Role Options
    const roleOptions = `
        @foreach($roles as $role)
        <option value="{{ $role->id }}">{{ $role->role_name }}</option>
        @endforeach
    `;
</script>
<script>
$(document).ready(function() {

    // --- TOGGLE ORGANIZATION SECTION ---
    function toggleOrganizationSection() {
        const role = $('#systemRoleSelect').val();
        // Role ID 1 is Super Admin
        if (role == 1) {
            $('#organizationSection').hide();
            $('.org-select, .division-select, .role-select, .manager-select').val('');
        } else {
            $('#organizationSection').show();
        }
    }

    // Initial check
    toggleOrganizationSection();

    $(document).on('change', '#systemRoleSelect', function() {
        toggleOrganizationSection();
    });

    // --- ADD ORGANIZATION ROW ---
    $("#addOrgRow").click(function() {
        let index = $(".org-row").length;
        let row = $(".org-row:first").clone();

        // Reset values and errors in the new row
        row.find("select").val("");
        row.find(".text-danger").text("");
        row.find(".form-control").removeClass("is-invalid");

        // Update name attributes to use current index
        row.find("select").each(function() {
            let name = $(this).attr('name');
            if (name) {
                $(this).attr('name', name.replace(/\[\d+\]/, `[${index}]`));
            }
        });

        $("#orgContainer").append(row);
    });

    // --- REMOVE ROW ---
    $(document).on("click", ".removeOrg", function() {
        if ($(".org-row").length > 1) {
            $(this).closest(".org-row").remove();
        } else {
            alert("At least one organization entry is required.");
        }
    });

    // --- LOAD MANAGER AJAX ---
    $(document).on("change", ".division-select, .role-select", function() {
        let row = $(this).closest(".org-row");
        let organizationId = row.find(".org-select").val();
        let divisionId = row.find(".division-select").val();
        let roleId = row.find(".role-select").val();

        if (!organizationId || !divisionId || !roleId) return;

        let managerSelect = row.find(".manager-select");
        
        $.ajax({
            url: '/get-managers',
            method: 'GET',
            data: { 
                organization_id: organizationId, 
                division_id: divisionId, 
                role_id: roleId 
            },
            beforeSend: function() {
                managerSelect.html('<option value="">Loading...</option>');
            },
            success: function(data) {
                managerSelect.empty();
                if (data.length === 0) {
                    managerSelect.append('<option value="">No Manager Available</option>');
                } else {
                    managerSelect.append('<option value="">Select Manager</option>');
                    $.each(data, function(key, item) {
                        managerSelect.append(`<option value="${item.id}">${item.name}</option>`);
                    });
                }
            },
            error: function() {
                managerSelect.html('<option value="">Error fetching data</option>');
            }
        });
    });

    // --- FORM VALIDATION + SWEETALERT ---
    $('#addUserForm').on('submit', function(e) {
        e.preventDefault();
        let isValid = true;

        // Reset error styling
        $('.text-danger').text('');
        $('.form-control').removeClass('is-invalid');

        function setError(el, message) {
            el.addClass('is-invalid');
            el.siblings('.text-danger').text(message);
        }

        let systemRole = $('#systemRoleSelect');
        if (systemRole.val() === '') {
            setError(systemRole, 'System Role is required');
            isValid = false;
        }

        // Basic Information Validation
        const fields = ['name', 'email', 'username'];
        fields.forEach(field => {
            let input = $(`input[name="${field}"]`);
            if (input.val().trim() === '') {
                setError(input, `${field.charAt(0).toUpperCase() + field.slice(1)} is required`);
                isValid = false;
            }
        });

        // Organization Validation (Skipped for Super Admin)
        if ($('#systemRoleSelect').val() != 1) {
            $('.org-row').each(function() {
                let org = $(this).find('.org-select');
                let div = $(this).find('.division-select');
                let role = $(this).find('.role-select');

                if (org.val() === '') { setError(org, 'Required'); isValid = false; }
                if (div.val() === '') { setError(div, 'Required'); isValid = false; }
                if (role.val() === '') { setError(role, 'Required'); isValid = false; }
            });
        }

        if (!isValid) return;

        // Confirmation Dialog
        Swal.fire({
            title: 'Are you sure add this data?',
            icon: 'warning',
            showCancelButton: true,
            reverseButtons: true,
            confirmButtonColor: '#4e73df',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#addUserForm')[0].submit(); 
            }
        });
    });

    // --- CLEAR FORM ---
    $(document).on('click', '.btn-clear-form', function(e) {
        e.preventDefault();
        
        let form = $('#addUserForm')[0];
        form.reset();

        // Clear error styling
        $('.text-danger').text('');
        $('.form-control').removeClass('is-invalid');

        // Reset dynamic organization section to default one row
        const originalRow = `
            <div class="row org-row mb-3 border-bottom pb-3">
                <div class="col-md-3">
                    <label class="small">Organization</label>
                    <select name="organizations[0][organization_id]" class="form-control org-select">
                        <option value="">Select</option>
                        @foreach($organizations as $org)
                            <option value="{{ $org->id }}">{{ $org->organization_name }}</option>
                        @endforeach
                    </select>
                    <small class="text-danger"></small>
                </div>
                <div class="col-md-2">
                    <label class="small">Division</label>
                    <select name="organizations[0][division_id]" class="form-control division-select">
                        <option value="">Select</option>
                        @foreach($divisions as $div)
                            <option value="{{ $div->id }}">{{ $div->division_name }}</option>
                        @endforeach
                    </select>
                    <small class="text-danger"></small>
                </div>
                <div class="col-md-2">
                    <label class="small">Role</label>
                    <select name="organizations[0][role_id]" class="form-control role-select">
                        <option value="">Select</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->role_name }}</option>
                        @endforeach
                    </select>
                    <small class="text-danger"></small>
                </div>
                <div class="col-md-3">
                    <label class="small">Manager</label>
                    <select name="organizations[0][manager_id]" class="form-control manager-select">
                        <option value="">Select Manager</option>
                    </select>
                    <small class="text-danger"></small>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-danger btn-block removeOrg">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>`;
        
        $('#orgContainer').html(originalRow);
        $('#organizationSection').show();
    });


    $(document).on('click', '.edit-btn', function() {
        const userId = $(this).data('id');
        $('#editUserId').val(userId);
        $('#editUserForm').attr('action', `/user/${userId}`);
        
        // Fetch user data
        $.ajax({
            url: `/user/${userId}`,
            method: 'GET',
            success: function(data) {
                // Populate basic fields
                $('#editSystemRoleSelect').val(data.system_role_id);
                $('#editName').val(data.name);
                $('#editEmail').val(data.email);
                $('#editUsername').val(data.username);
                
                // Toggle organization section
                toggleEditOrganizationSection();
                
                // Populate organizations
                populateEditOrganizations(data.organizations);
                
                // Show modal
                $('#editModal').modal('show');
            },
            error: function() {
                Swal.fire('Error', 'Failed to load user data', 'error');
            }
        });
    });

    // --- TOGGLE EDIT ORGANIZATION SECTION ---
    function toggleEditOrganizationSection() {
        const role = $('#editSystemRoleSelect').val();
        if (role == 1) {
            $('#editOrganizationSection').hide();
            $('#editOrgContainer').empty();
        } else {
            $('#editOrganizationSection').show();
        }
    }
// --- POPULATE ORGANIZATIONS (FIXED) ---
    function populateEditOrganizations(organizations) {
        let html = '';
        
        if (organizations.length === 0) {
            html = getDefaultOrgRow(0);
            $('#editOrgContainer').html(html);
            return;
        }
        
        organizations.forEach((org, index) => {
            // Build organization dropdown HTML
            let orgOptions = `<option value="">Select</option>`;
            @foreach($organizations as $o)
                orgOptions += `<option value="{{ $o->id }}" ${org.organization_id == {{ $o->id }} ? 'selected' : ''}>{{ $o->organization_name }}</option>`;
            @endforeach
            
            // Build division dropdown HTML  
            let divOptions = `<option value="">Select</option>`;
            @foreach($divisions as $d)
                divOptions += `<option value="{{ $d->id }}" ${org.division_id == {{ $d->id }} ? 'selected' : ''}>{{ $d->division_name }}</option>`;
            @endforeach
            
            // Build role dropdown HTML
            let roleOptions = `<option value="">Select</option>`;
            @foreach($roles as $r)
                roleOptions += `<option value="{{ $r->id }}" ${org.role_id == {{ $r->id }} ? 'selected' : ''}>{{ $r->role_name }}</option>`;
            @endforeach
            
            // ✅ BUILD MANAGER DROPDOWN DENGAN DATA YANG UDAH ADA
            let managerOptions = `<option value="">Select Manager</option>`;
            
            html += `
            <div class="row org-row mb-3 border-bottom pb-3" data-manager-id="${org.manager_id || ''}">
                <div class="col-md-3">
                    <label class="small">Organization</label>
                    <select name="organizations[${index}][organization_id]" class="form-control org-select">
                        ${orgOptions}
                    </select>
                    <small class="text-danger"></small>
                </div>
                <div class="col-md-2">
                    <label class="small">Division</label>
                    <select name="organizations[${index}][division_id]" class="form-control division-select">
                        ${divOptions}
                    </select>
                    <small class="text-danger"></small>
                </div>
                <div class="col-md-2">
                    <label class="small">Role</label>
                    <select name="organizations[${index}][role_id]" class="form-control role-select">
                        ${roleOptions}
                    </select>
                    <small class="text-danger"></small>
                </div>
                <div class="col-md-3">
                    <label class="small">Manager</label>
                    <select name="organizations[${index}][manager_id]" class="form-control manager-select">
                        ${managerOptions}
                    </select>
                    <small class="text-danger"></small>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-danger btn-block removeOrg">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>`;
        });
        
        $('#editOrgContainer').html(html);
        
        // ✅ LOAD MANAGER UNTUK SETIAP ROW SETELAH HTML DI-GENERATE
        $('#editOrgContainer .org-row').each(function(index) {
            let row = $(this);
            let orgId = row.find('.org-select').val();
            let divId = row.find('.division-select').val();
            let roleId = row.find('.role-select').val();
            let savedManagerId = row.data('manager-id');
            
            if (orgId && divId && roleId) {
                loadManagers(row, orgId, divId, roleId, savedManagerId);
            }
        });
    }
    function getDefaultOrgRow(index) {
        return `
        <div class="row org-row mb-3 border-bottom pb-3">
            <div class="col-md-3">
                <label class="small">Organization</label>
                <select name="organizations[${index}][organization_id]" class="form-control org-select">
                    <option value="">Select</option>
                    ${organizationOptions}
                </select>
                <small class="text-danger"></small>
            </div>
            <div class="col-md-2">
                <label class="small">Division</label>
                <select name="organizations[${index}][division_id]" class="form-control division-select">
                    <option value="">Select</option>
                    ${divisionOptions}
                </select>
                <small class="text-danger"></small>
            </div>
            <div class="col-md-2">
                <label class="small">Role</label>
                <select name="organizations[${index}][role_id]" class="form-control role-select">
                    <option value="">Select</option>
                    ${roleOptions}
                </select>
                <small class="text-danger"></small>
            </div>
            <div class="col-md-3">
                <label class="small">Manager</label>
                <select name="organizations[${index}][manager_id]" class="form-control manager-select">
                    <option value="">Select Manager</option>
                </select>
                <small class="text-danger"></small>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="button" class="btn btn-danger btn-block removeOrg">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>`;
    }

    $("#editAddOrgRow").click(function() {
        let index = $("#editOrgContainer .org-row").length;
        let row = getDefaultOrgRow(index);
        $("#editOrgContainer").append(row);
    });

    // --- EDIT FORM VALIDATION + SUBMIT ---
    $('#editUserForm').on('submit', function(e) {
        e.preventDefault();
        let isValid = true;

        // Reset error styling
        $('.text-danger').text('');
        $('.form-control').removeClass('is-invalid');

        function setError(el, message) {
            el.addClass('is-invalid');
            el.siblings('.text-danger').text(message);
        }

        let systemRole = $('#editSystemRoleSelect');
        if (systemRole.val() === '') {
            setError(systemRole, 'System Role is required');
            isValid = false;
        }

        // Basic Information Validation
        const fields = [
            {id: 'editName', name: 'Name'},
            {id: 'editEmail', name: 'Email'}, 
            {id: 'editUsername', name: 'Username'}
        ];
        
        fields.forEach(field => {
            let input = $('#' + field.id);
            if (input.val().trim() === '') {
                setError(input, `${field.name} is required`);
                isValid = false;
            }
        });

        // Organization Validation (Skipped for Super Admin)
        if ($('#editSystemRoleSelect').val() != 1) {
            $('.org-row', '#editOrgContainer').each(function() {
                let org = $(this).find('.org-select');
                let div = $(this).find('.division-select');
                let role = $(this).find('.role-select');

                if (org.val() === '') { setError(org, 'Required'); isValid = false; }
                if (div.val() === '') { setError(div, 'Required'); isValid = false; }
                if (role.val() === '') { setError(role, 'Required'); isValid = false; }
            });
        }

        if (!isValid) return;

        // Confirmation Dialog
        Swal.fire({
            title: 'Are you sure update this data?',
            icon: 'warning',
            showCancelButton: true,
            reverseButtons: true,
            confirmButtonColor: '#4e73df',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#editUserForm')[0].submit(); 
            }
        });
    });

    // --- CLEAR EDIT FORM ---
    $(document).on('click', '.btn-clear-edit-form', function(e) {
        e.preventDefault();
        $('#editUserForm')[0].reset();
        $('.text-danger').text('');
        $('.form-control').removeClass('is-invalid');
        $('#editOrgContainer').html(getDefaultOrgRow(0));
        $('#editOrganizationSection').show();
    });

    // --- SYSTEM ROLE CHANGE FOR EDIT ---
    $(document).on('change', '#editSystemRoleSelect', function() {
        toggleEditOrganizationSection();
    });

    // --- REUSE MANAGER AJAX FOR EDIT ---
    $(document).on("change", "#editOrgContainer .division-select, #editOrgContainer .role-select", function() {
        let row = $(this).closest(".org-row");
        let organizationId = row.find(".org-select").val();
        let divisionId = row.find(".division-select").val();
        let roleId = row.find(".role-select").val();

        if (!organizationId || !divisionId || !roleId) return;

        loadManagers(row, organizationId, divisionId, roleId);
    });

    // Helper function to load managers
    // Helper function to load managers (UPDATED)
    function loadManagers(row, orgId, divId, roleId, savedManagerId = null) {
        let managerSelect = row.find(".manager-select");
        
        $.ajax({
            url: '/get-managers',
            method: 'GET',
            data: { 
                organization_id: orgId, 
                division_id: divId, 
                role_id: roleId 
            },
            beforeSend: function() {
                managerSelect.html('<option value="">Loading...</option>');
            },
            success: function(data) {
                managerSelect.empty();
                managerSelect.append('<option value="">Select Manager</option>');
                
                if (data.length === 0) {
                    managerSelect.append('<option value="">No Manager Available</option>');
                } else {
                    $.each(data, function(key, item) {
                        let selected = (savedManagerId && item.id == savedManagerId) ? 'selected' : '';
                        managerSelect.append(`<option value="${item.id}" ${selected}>${item.name}</option>`);
                    });
                }
            },
            error: function() {
                managerSelect.html('<option value="">Error fetching data</option>');
            }
        });
    }

    // --- REUSE REMOVE ROW FOR EDIT ---
    $(document).on("click", "#editOrgContainer .removeOrg", function() {
        if ($("#editOrgContainer .org-row").length > 1) {
            $(this).closest(".org-row").remove();
            // Reindex remaining rows
            $("#editOrgContainer .org-row").each(function(index) {
                $(this).find('select').each(function() {
                    let name = $(this).attr('name');
                    if (name) {
                        $(this).attr('name', name.replace(/\$(\d+)\$/g, `[${index}]`));
                    }
                });
            });
        } else {
            alert("At least one organization entry is required.");
        }
    });

      $('.deleteBtn').on('click', function() {
        let id = $(this).data('id');
        let username = $(this).data('username');

        Swal.fire({
            title: 'Are you sure delete this data?',
            text: `Delete ${username}? This cannot be undone.`,
            icon: 'warning',
            showCancelButton: true,
            reverseButtons: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {

                let form = $('#globalDeleteForm');
                form.attr('action', '/user/' + id);
                form.submit();

            }
        });
    });

        let searchTimer;

    $('#searchInput').on('keyup', function () {
        clearTimeout(searchTimer);

        let value = $(this).val();

        searchTimer = setTimeout(function () {
            let url = new URL(window.location.href);

            if (value) {
                url.searchParams.set('search', value);
            } else {
                url.searchParams.delete('search');
            }

            // biar perPage tetap kebawa
            let perPage = $('select[name="perPage"]').val();
            if (perPage) {
                url.searchParams.set('perPage', perPage);
            }

            window.location.href = url.toString();
        }, 400); // delay biar smooth
    });
    
        
});
</script>
@endpush