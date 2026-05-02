@extends('layouts.app')

@section('title', 'Workflow')

@section('content')

<div class="container-fluid">

    @include('components.alert')

    <h1 class="h4 mb-4 text-gray-800">Work Flow</h1>

    <div class="card shadow mb-4">
        <div class="card-body">

         <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h6 class="font-weight-bold text-primary">Workflow List</h6>
                    <input 
                        type="text" 
                        id="searchInput"
                        name="search"
                        class="form-control"
                        placeholder="Search document type"
                        value="{{ request('search') }}"
                        style="width: 250px;"
                    >
                </div>
                <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addWorkflowModal">
                    <i class="fas fa-plus"></i> Add Workflow
                </button>
            </div>
            {{-- HEADER --}}


            {{-- TABLE --}}
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="bg-light">
                        <tr>
                            <th>Document Type</th>
                            <th>Organization</th>
                            <th>Workflow</th>
                            <th width="150" class="text-center">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($workflow as $item)
                            <tr>
                                <td>{{ $item->document_type }}</td>
                                <th>{{ $item->organization->organization_name }}</th>

                                {{-- WORKFLOW STEPS --}}
                             <td>
                                @php
                                    $tierColors = [
                                        1 => 'primary',
                                        2 => 'success',
                                        3 => 'warning',
                                        4 => 'danger',
                                        5 => 'info',
                                    ];

                                    $fallbackColors = ['secondary', 'dark', 'light'];
                                @endphp

                                @forelse($item->workflowstep as $step)
                                    @php
                                        $color = $tierColors[$step->tier] 
                                            ?? $fallbackColors[($step->tier - 1) % count($fallbackColors)];
                                    @endphp

                                    <span class="badge badge-{{ $color }}">
                                        Tier {{ $step->tier }} - {{ optional($step->division)->division_name ?? '-' }}
                                        <br>
                                        <medium>SLA: {{ $step->sla_days ?? '-' }} days</medium>
                                        <br>
                                        <small>
                                            Min Role: {{ optional($step->role)->role_name ?? '-' }}
                                        </small>
                                    </span>

                                    @if(!$loop->last)
                                        <i class="fas fa-arrow-right mx-1"></i>
                                    @endif
                                @empty
                                    <span class="text-muted">No workflow</span>
                                @endforelse
                            </td>

                                <td class="text-center">
<button class="btn btn-sm btn-light edit-btn"
    data-id="{{ $item->id }}">
    <i class="fas fa-edit text-primary"></i>
</button>

                                    <button class="btn btn-sm btn-light deleteBtn"
                                        data-id="{{ $item->id }}"
                                        data-doctype="{{ $item->document_type }}"
                                        data-toggle="modal"
                                        data-target="#deleteModal">
                                        <i class="fas fa-trash text-danger"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">
                                    No data available
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

                        <div class="d-flex justify-content-between align-items-center mt-3">

                <!-- SHOW ENTRIES -->
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

                <!-- PAGINATION -->
                <div>
                    @if ($workflow->lastPage() > 1)
                       {{ $workflow->onEachSide(2)->links('pagination::bootstrap-4') }}
                    @else
                        <ul class="pagination">
                            <li class="page-item disabled">
                                <span class="page-link">«</span>
                            </li>
                            <li class="page-item active">
                                <span class="page-link">1</span>
                            </li>
                            <li class="page-item disabled">
                                <span class="page-link">»</span>
                            </li>
                        </ul>
                    @endif
                </div>

            </div>
        </div>
    </div>
</div>

{{-- ================= MODAL CREATE WORKFLOW ================= --}}
<div class="modal fade" id="addWorkflowModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <form id="workflowForm" method="POST" action="{{ route('workflow.store') }}">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">Create Workflow</h5>
                    <button class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">

                    {{-- DOCUMENT TYPE --}}
                    <div class="form-group">
                        <label>Document Type</label>
                        <input type="text" id="documentType" name="document_type" class="form-control">
                        <small class="text-danger" id="error-document-type"></small>
                    </div>

                    {{-- ORGANIZATION --}}
                    <div class="form-group">
                        <label class="font-weight-bold">Organization</label>
                        <select name="organization_id" class="form-control" id="organizationId">
                            <option value="">Select Organization</option>
                            @foreach($organization as $org)
                                <option value="{{ $org->id }}">{{ $org->organization_name }}</option>
                            @endforeach
                        </select>
                        <small class="text-danger" id="error-organization"></small>
                    </div>

                    <hr>

                    {{-- STEPS --}}
                    <h6 class="font-weight-bold">Approval Division</h6>
                    <small class="text-danger" id="error-division"></small>

                    <div id="workflowContainer">
                    <div class="form-row align-items-end workflow-row mb-3">

                        <div class="col-sm-1">
                            <label>Tier</label>
                            <input type="number"
                                class="form-control tierInput"
                                name="steps[0][tier]"
                                value="1"
                                readonly>
                        </div>

                        <div class="col-md-3">
                            <label>Division</label>
                            <select class="form-control division-select"
                                    name="steps[0][division_id]">
                                <option value="">Select Division</option>
                                @foreach($divisions as $div)
                                    <option value="{{ $div->id }}">
                                        {{ $div->division_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label>SLA (Days)</label>
                            <input type="number"
                                class="form-control slaInput"
                                name="steps[0][sla_days]">
                        </div>

                        <div class="col-md-3">
                            <label>Min Approver Level</label>
                            <select class="form-control role-select"
                                name="steps[0][min_role_level]">
                                <option value="">Select Role</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->role_level }}">
                                        {{ $role->role_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                         <div class="col-md-2">
                                <button type="button" class="btn btn-danger removeWorkflow w-100">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>

                                        </div>

   

                </div>
                
                    <button type="button" class="btn btn-outline-primary btn-sm" id="addWorkflowRow">
                        <i class="fas fa-plus"></i> Add Tier
                    </button>


                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="clearWorkflow">
                        Clear
                    </button>

                    <button type="submit" class="btn btn-primary" id="saveWorkflow">
                        Save
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>

 <form id="globalDeleteForm" method="POST" style="display:none;">
    @csrf
    @method('DELETE')
</form>


@endsection
@push('modals')
{{-- ================= MODAL EDIT WORKFLOW ================= --}}
<div class="modal fade" id="editWorkflowModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <form id="editWorkflowForm" method="POST">
                @csrf
                @method('PUT')

                <div class="modal-header">
                    <h5 class="modal-title">Edit Workflow</h5>
                    <button class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">

                    {{-- DOCUMENT TYPE --}}
                    <div class="form-group">
                        <label>Document Type</label>
                        <input type="text" id="editDocumentType" name="document_type" class="form-control">
                        <small class="text-danger" id="edit-error-document-type"></small>
                    </div>

                    {{-- ORGANIZATION --}}
                    <div class="form-group">
                        <label>Organization</label>
                        <select name="organization_id" class="form-control" id="editOrganizationId">
                            <option value="">Select Organization</option>
                            @foreach($organization as $org)
                                <option value="{{ $org->id }}">{{ $org->organization_name }}</option>
                            @endforeach
                        </select>
                        <small class="text-danger" id="edit-error-organization"></small>
                    </div>

                    <hr>

                    <h6 class="font-weight-bold">Approval Division</h6>
                    <small class="text-danger" id="edit-error-division"></small>

                    <div id="editWorkflowContainer"></div>

                    <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="editAddWorkflowRow">
                        <i class="fas fa-plus"></i> Add Tier
                    </button>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-warning" id="editClearWorkflow">
                        Clear
                    </button>

                    <button type="submit" class="btn btn-primary">Update</button>
                </div>

            </form>

        </div>
    </div>
</div>
@endpush

{{-- ================= JS ================= --}}
@push('scripts')
<script>
let originalEditData = null;
$(document).ready(function () {
    $('#clearWorkflow').on('click', function () {
        resetWorkflowForm();
    });

    // =====================
    // ADD TIER
    // =====================
$("#addWorkflowRow").click(function () {

    let row = $(".workflow-row:first").clone();

    row.find("select").val("");
    row.find(".slaInput").val("");
    row.find(".role-select").val("");

    $("#workflowContainer").append(row);

    reorderTier("#workflowContainer");
});

    // =====================
    // REMOVE TIER
    // =====================
$(document).on("click", ".removeWorkflow", function () {
    if ($("#workflowContainer .workflow-row").length > 1) {
        $(this).closest(".workflow-row").remove();
        reorderTier("#workflowContainer");
    }
});



    // =====================
    // SAVE WORKFLOW VALIDATION
    // =====================
    $('#workflowForm').on('submit', function (e) {
        e.preventDefault();

        let isValid = true;

        let documentType = $('#documentType').val().trim();
        let errorDoc = $('#error-document-type');
        let errorDivision = $('#error-division');

        errorDoc.text('');
        errorDivision.text('');

        $('#documentType').removeClass('is-invalid');
        $('.division-select').removeClass('is-invalid');

        let organization = $('#organizationId').val();
        let errorOrg = $('#error-organization');

        errorOrg.text('');
        $('#organizationId').removeClass('is-invalid');

        if (!organization) {
            errorOrg.text('Organization is required');
            $('#organizationId').addClass('is-invalid');
            isValid = false;
        }

        // VALIDASI DOCUMENT TYPE
        if (documentType.length < 1) {
            errorDoc.text('Document Type is required');
            $('#documentType').addClass('is-invalid');
            isValid = false;
        } else if (documentType.length > 24) {
            errorDoc.text('Max 24 characters allowed');
            $('#documentType').addClass('is-invalid');
            isValid = false;
        }

        // VALIDASI DIVISION
        let divisions = [];
        let hasDivision = false;

        $('.workflow-row').each(function () {
            let division = $(this).find('.division-select').val();
            if (division) {
                hasDivision = true;
                divisions.push(division);
            }
        });

        if (!hasDivision) {
            errorDivision.text('At least 1 approval division is required');
            isValid = false;
        }

        // DUPLICATE CHECK
        let duplicate = divisions.some((item, index) => divisions.indexOf(item) !== index);

        if (duplicate) {
            Swal.fire({
                icon: 'error',
                title: 'Duplicate Division',
                text: 'Each tier must have different division'
            });
            return;
        }

        if (!isValid) return;

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
                this.submit();
            }
        });

});

$(document).on('click', '.edit-btn', function () {

    let id = $(this).data('id');

    // reset container
    $("#editWorkflowContainer").html('');

    $.ajax({
        url: `/workflow/${id}/edit`,
        type: 'GET',
        success: function (res) {

            originalEditData = res; 

            // set action form
            $('#editWorkflowForm').attr('action', `/workflow/${id}`);

            // fill header
            $('#editDocumentType').val(res.document_type);
            $('#editOrganizationId').val(res.organization_id);

            // fill steps
            res.workflowstep.forEach((step, index) => {

                let row = `
                    <div class="form-row align-items-end workflow-row mb-3">

                        <div class="col-md-1">
                            <label>Tier</label>
                            <input type="number"
                                class="form-control"
                                name="steps[${index}][tier]"
                                value="${step.tier}"
                                readonly>
                        </div>

                        <div class="col-md-3">
                            <label>Division</label>
                            <select class="form-control"
                                name="steps[${index}][division_id]">
                                @foreach($divisions as $div)
                                    <option value="{{ $div->id }}"
                                        ${step.division_id == {{ $div->id }} ? 'selected' : ''}>
                                        {{ $div->division_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label>SLA (Days)</label>
                            <input type="number"
                                class="form-control"
                                name="steps[${index}][sla_days]"
                                value="${step.sla_days ?? ''}">
                        </div>

                        <div class="col-md-3">
                            <label>Min Role</label>
                            <select class="form-control"
                                name="steps[${index}][min_role_level]">
                                <option value="">Select Role</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->role_level }}"
                                        ${step.min_role_level == {{ $role->role_level }} ? 'selected' : ''}>
                                        {{ $role->role_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <button type="button" class="btn btn-danger removeEditWorkflow w-100">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>

                    </div>
                `;

                $("#editWorkflowContainer").append(row);
            });

            $('#editWorkflowModal').modal('show');
        }
    });

});

$(document).on('click', '#editAddWorkflowRow', function () {

    let index = $("#editWorkflowContainer .workflow-row").length;

    let row = `
        <div class="form-row align-items-end workflow-row mb-3">

            <div class="col-md-1">
            <label>Tier</label>
                <input type="number" class="form-control" name="steps[${index}][tier]" value="${index+1}" readonly>
            </div>

            <div class="col-md-3">
            <label>Division</label>
                <select class="form-control" name="steps[${index}][division_id]">
                    <option value="">Select Division</option>
                    @foreach($divisions as $div)
                        <option value="{{ $div->id }}">{{ $div->division_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
            <label>SLA (Days)</label>
                <input type="number" class="form-control" name="steps[${index}][sla_days]">
            </div>

            <div class="col-md-3">
                <label>Min Role</label>
                <select class="form-control"
                    name="steps[${index}][min_role_level]">
                    <option value="">Select Role</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->role_level }}">
                            {{ $role->role_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <button type="button" class="btn btn-danger removeEditWorkflow w-100">
                    <i class="fas fa-trash"></i>
                </button>
            </div>

        </div>
    `;

    $("#editWorkflowContainer").append(row);
});

$(document).on('click', '.removeEditWorkflow', function () {

    // hanya boleh remove kalau lebih dari 1 row
    if ($("#editWorkflowContainer .workflow-row").length > 1) {
        $(this).closest(".workflow-row").remove();
       reorderTier("#editWorkflowContainer");
    }

});
    function resetWorkflowForm() {
        // reset document type
        $('#documentType').val('').removeClass('is-invalid');
        $('#error-document-type').text('');
          $('#error-organization').text('');

        // reset division error
        $('#error-division').text('');

        // reset workflow rows jadi 1 lagi
        let firstRow = $("#workflowContainer .workflow-row:first");

        $("#workflowContainer").html(firstRow);

        // reset isi row pertama
        firstRow.find('.division-select').val('').removeClass('is-invalid');
        firstRow.find('.slaInput').val('');

        // reset tier
        firstRow.find('.tierInput').val(1);
    }

    function renderEditSteps(steps) {
    $("#editWorkflowContainer").html('');

    steps.forEach((step, index) => {

        let row = `
            <div class="form-row align-items-end workflow-row mb-3">

                <div class="col-md-2">
                <label>Tier</label>
                    <input type="number"
                        class="form-control"
                        name="steps[${index}][tier]"
                        value="${step.tier}"
                        readonly>
                </div>

                <div class="col-md-5">
                <label>Division</label>
                    <select class="form-control"
                        name="steps[${index}][division_id]">
                        @foreach($divisions as $div)
                            <option value="{{ $div->id }}"
                                ${step.division_id == {{ $div->id }} ? 'selected' : ''}>
                                {{ $div->division_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                <label>SLA (Days)</label>
                    <input type="number"
                        class="form-control"
                        name="steps[${index}][sla_days]"
                        value="${step.sla_days ?? ''}">
                </div>

                <div class="col-md-2">
                    <button type="button" class="btn btn-danger removeEditWorkflow w-100">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>

            </div>
        `;

        $("#editWorkflowContainer").append(row);
    });
}

$(document).on('click', '#editClearWorkflow', function () {

    if (!originalEditData) return;

    // reset field utama
    $('#editDocumentType').val(originalEditData.document_type);
    $('#editOrganizationId').val(originalEditData.organization_id);

    // reset error (PAKAI EDIT VERSION)
    $('#edit-error-document-type').text('');
    $('#edit-error-organization').text('');
    $('#edit-error-division').text('');

    // reset step ke data awal
    renderEditSteps(originalEditData.workflowstep);
});

function reorderTier(container) {
    $(container + " .workflow-row").each(function (index) {

        // tier
        $(this).find('input[name*="[tier]"]')
            .val(index + 1)
            .attr('name', `steps[${index}][tier]`);

        // division
        $(this).find('select[name*="[division_id]"]')
            .attr('name', `steps[${index}][division_id]`);

        $(this).find('select[name*="[min_role_level]"]')
        .attr('name', `steps[${index}][min_role_level]`);

        // sla
        $(this).find('input[name*="[sla_days]"]')
            .attr('name', `steps[${index}][sla_days]`);
    });
}


$('#editWorkflowForm').on('submit', function (e) {
    e.preventDefault();

    let isValid = true;

    let documentType = $('#editDocumentType').val().trim();
    let errorDoc = $('#edit-error-document-type');
    let errorOrg = $('#edit-error-organization');
    let errorDivision = $('#edit-error-division');

    errorDoc.text('');
    errorOrg.text('');
    errorDivision.text('');

    $('#editDocumentType').removeClass('is-invalid');
    $('#editOrganizationId').removeClass('is-invalid');

    let organization = $('#editOrganizationId').val();

    // =====================
    // VALIDASI ORGANIZATION
    // =====================
    if (!organization) {
        errorOrg.text('Organization is required');
        $('#editOrganizationId').addClass('is-invalid');
        isValid = false;
    }

    // =====================
    // VALIDASI DOCUMENT TYPE
    // =====================
    if (documentType.length < 1) {
        errorDoc.text('Document Type is required');
        $('#editDocumentType').addClass('is-invalid');
        isValid = false;
    } else if (documentType.length > 24) {
        errorDoc.text('Max 24 characters allowed');
        $('#editDocumentType').addClass('is-invalid');
        isValid = false;
    }

    // =====================
    // VALIDASI DIVISION
    // =====================
    let divisions = [];
    let hasDivision = false;

    $('#editWorkflowContainer .workflow-row').each(function () {
        let division = $(this).find('select[name*="[division_id]"]').val();

        if (division) {
            hasDivision = true;
            divisions.push(division);
        }
    });

    if (!hasDivision) {
        errorDivision.text('At least 1 approval division is required');
        isValid = false;
    }

    // =====================
    // DUPLICATE CHECK
    // =====================
    let duplicate = divisions.some((item, index) => divisions.indexOf(item) !== index);

    if (duplicate) {
        Swal.fire({
            icon: 'error',
            title: 'Duplicate Division',
            text: 'Each tier must have different division'
        });
        return;
    }

    if (!isValid) return;

    // =====================
    // SWEETALERT CONFIRM EDIT
    // =====================
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
            this.submit();
        }
    });
});

$('.deleteBtn').on('click', function() {
        let id = $(this).data('id');
        let doctype = $(this).data('doctype');

        Swal.fire({
            title: 'Are you sure delete this data?',
            text: `Delete ${doctype}? This cannot be undone.`,
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
                form.attr('action', '/workflow/' + id);
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