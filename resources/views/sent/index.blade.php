@extends('layouts.app')
@section('title', 'Request')

<style>
    .document-filter .form-control-sm {
        height: 34px;
    }

    .document-filter .select2-container--bootstrap4 .select2-selection--single {
        height: 34px !important;
        min-height: 34px !important;
    }

    .document-filter .date-filter {
        margin-top: 1rem;
    }

    input[type="date"].form-control-sm {
    padding-left: 10px !important;
    padding-right: 10px !important;
}
.form-label {
    display: inline-block;
    margin-bottom: 0.35rem !important;
}
    .hover-shadow {
        transition: all 0.25s ease;
    }
    .hover-shadow:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.08) !important;
    }

    .share-user-row {
        align-items: center;
    }

    .share-user-row .select2-container {
        flex: 1 1 auto;
        width: auto !important;
    }

    .share-user-row .btn-remove-user {
        height: 38px;
    }

    .select2-container {
        width: 100% !important;
    }

    .select2-container--bootstrap4 .select2-selection--single {
        height: 31px !important;
        min-height: 31px !important;
        border: 1px solid #ced4da !important;
        border-radius: 0.25rem !important;
        background: #fff !important;
        padding: 0 !important;
        box-shadow: none !important;
        position: relative !important;
        display: flex !important;
        align-items: center !important;
        overflow: hidden !important;
    }

    .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
        display: flex !important;
        align-items: center !important;
        line-height: 1.2 !important;
        padding: 0 36px 0 12px !important;
        color: #111827 !important;
        font-size: 0.875rem !important;
        width: 100% !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        white-space: nowrap !important;
    }

    .select2-container--bootstrap4 .select2-selection--single .select2-selection__placeholder {
        color: #6b7280 !important;
    }

    .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow {
        height: 100% !important;
        width: 28px !important;
        right: 2px !important;
        background: transparent !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
    }

    .select2-container--bootstrap4 .select2-selection__clear {
        color: #111827 !important;
        font-size: 18px !important;
        right: 28px !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
        margin: 0 !important;
        line-height: 1 !important;
    }

    .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow b {
        border-color: #111827 transparent transparent transparent !important;
        border-width: 6px 5px 0 5px !important;
        margin-left: -4px !important;
        margin-top: -2px !important;
    }

    .select2-container--bootstrap4.select2-container--focus .select2-selection--single {
        border-color: #111827 !important;
        box-shadow: none !important;
    }

    .select2-container--bootstrap4 .select2-dropdown {
        border: 2px solid #1f1f1f !important;
        border-radius: 0 !important;
        box-shadow: none !important;
    }

    .select2-container--bootstrap4 .select2-results__option {
        padding: 10px 12px !important;
        font-size: 0.95rem !important;
        color: #111827 !important;
        background: #fff !important;
    }

    .select2-container--bootstrap4 .select2-results__option--highlighted {
        background-color: #f3f4f6 !important;
        color: #111827 !important;
    }

    .select2-container--bootstrap4 .select2-search__field {
        border: 1px solid #d1d5db !important;
        border-radius: 0 !important;
        padding: 10px 12px !important;
        outline: none !important;
    }
</style>

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Request</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <!-- @if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif -->

    {{-- Documents Section - Hanya tampil jika sedang di dalam folder --}}
 
        <!-- Top Bar -->
        <div class="d-flex justify-content-between mb-3">
            <div>
                <button class="btn btn-success mr-2" id="bulkExportBtn" disabled>
                    <i class="fas fa-file-export"></i> Bulk Download
                </button>
                <!-- <button class="btn btn-primary" id="bulkApproveBtn" disabled>
                    <i class="fas fa-check-circle"></i> Bulk Approve
                </button> -->
            </div>

        </div>
    <!-- Filter -->
<!-- Filter -->
<div class="card shadow-sm border-0 mb-4 document-filter">
    <div class="card-body">
        <form method="GET" action="{{ route('sent.index') }}">
            @if(request()->filled('perPage'))
                <input type="hidden" name="perPage" value="{{ request('perPage') }}">
            @endif
            <div class="row g-3 align-items-end">
                <!-- Document Name -->
                <div class="col-lg-3 col-md-4">
                    <label class="form-label small font-weight-bold text-muted mb-2">Document Name</label>
                    <input type="text" id="searchInput" name="search" class="form-control form-control-sm" placeholder="Search document name..." value="{{ request('search') }}">
                </div>

                <!-- Status -->
                <div class="col-lg-2 col-md-4">
                    <label class="form-label small font-weight-bold text-muted mb-2">Status</label>
                    <select class="form-control form-control-sm" name="status">
                        <option value="">All Status</option>
                        <option value="Need Approval" {{ request('status') == 'Need Approval' ? 'selected' : '' }}>Need Approval</option>
                        <option value="In Progress" {{ request('status') == 'In Progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="Approved" {{ request('status') == 'Approved' ? 'selected' : '' }}>Approved</option>
                        <option value="Rejected" {{ request('status') == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>

                <!-- Requester -->
                <div class="col-lg-3 col-md-4">
                    <label class="form-label small font-weight-bold text-muted mb-2">Requester</label>
                    <select class="form-control form-control-sm select2" id="requesterSelect" name="requester_id">
                        <option value="">All Requesters</option>
                        @foreach($userOptions as $user)
                            <option value="{{ $user->id }}" {{ request('requester_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->username }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Addressee -->
                <div class="col-lg-4 col-md-4">
                    <label class="form-label small font-weight-bold text-muted mb-2">Addressee</label>
                    <select class="form-control form-control-sm select2" id="addresseeSelect" name="addressee_id">
                        <option value="">All Addressees</option>
                        @foreach($addresseeOptions as $user)
                            <option value="{{ $user->id }}" {{ request('addressee_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->username }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- From Date -->
                <div class="col-lg-2 col-md-3 col-6 date-filter">
                    <label class="form-label small font-weight-bold text-muted mb-2">From</label>
                    <input type="date" class="form-control form-control-sm px-2" name="from_date" value="{{ request('from_date') }}">
                </div>

                <!-- To Date -->
                <div class="col-lg-2 col-md-3 col-6 date-filter">
                    <label class="form-label small font-weight-bold text-muted mb-2">To</label>
                    <input type="date" class="form-control form-control-sm px-2" name="to_date" value="{{ request('to_date') }}">
                </div>

                <!-- Filter Actions -->
                <div class="col-lg-8 col-md-6 col-12 text-end mt-3">
                    <a href="{{ route('sent.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-undo me-1"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>
        
        <!-- Documents -->
        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="bg-light">
                            <tr>
                                <th width="40"><input type="checkbox" id="selectAll"></th>
                                <th>Document Name</th>
                                <th>Status</th>
                                <th>Folder</th>
                                <th>Created Time</th>
                                <th>Last Modified</th>
                                <th>Requester</th>
                                <th>Addressee</th>
                                <th width="180">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($documents as $document)
                            <tr>
                                <td><input type="checkbox" class="rowCheckbox" value="{{ $document->id }}"></td>
                                <td>
                                    <strong>{{ $document->document_name }}</strong>
                                </td>
                                <td>
                                    <span class="badge
                                        @if($document->status == 'Approved') badge-success
                                        @elseif($document->status == 'Rejected') badge-danger
                                        @elseif($document->status == 'In Progress' || $document->status == 'In Progrress') badge-info
                                        @elseif($document->status == 'Need Approval') badge-warning
                                        @else badge-secondary
                                        @endif">
                                        {{ $document->status }}
                                    </span>
                                </td>
                                <td>{{ $document->folder?->full_path ?? '-' }}</td>
                                <td>{{ $document->created_at->format('d M Y, H:i') }}</td>
                                <td>{{ $document->updated_at->format('d M Y, H:i') }}</td>
                                <td>
                                    {{ $document->requester?->name ?? '-' }} 
                                    ({{ $document->requester?->username ?? '-' }})
                                </td>
                                <td>
                                    @php
                                        $addressees = $document->documentApprovals
                                            ->where('is_requester', false)
                                            ->map(fn($approval) => $approval->approver?->name)
                                            ->filter()
                                            ->unique()
                                            ->values();
                                    @endphp

                                    @if($addressees->isNotEmpty())
                                        {{ $addressees->implode(', ') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('sent.preview', $document->id) }}" class="btn btn-sm btn-light" title="View">
                                        <i class="fas fa-eye text-secondary"></i>
                                    </a>
                                    <a href="{{ route('sent.download', $document->id) }}" 
                                        class="btn btn-sm btn-light" 
                                        title="Download"
                                        download>
                                            <i class="fas fa-download text-success"></i>
                                        </a>
                                    @if($document->status == 'Need Approval' && $document->documentApprovals
                                        ->where('is_requester', false)
                                        ->isNotEmpty() && $document->documentApprovals
                                        ->where('is_requester', false)
                                        ->every(fn ($approval) => $approval->status == 'Pending' && ! $approval->flag_open))
                                        <form method="POST" action="{{ route('sent.cancel', $document->id) }}" class="d-inline cancel-document-form">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-light" title="Withdraw" data-document-name="{{ $document->document_name }}">
                                                <i class="fas fa-ban text-danger"></i>
                                            </button>
                                        </form>
                                    @endif
 @if($document->status == 'Approved')
            <button
                type="button"
                class="btn btn-sm btn-light btn-share-document"
                data-document-id="{{ $document->id }}"
                title="Share">
                <i class="fas fa-share-alt text-info"></i>
            </button>
            @endif
                                    <button type="button"
                                            class="btn btn-sm btn-light btn-move-folder"
                                            data-document-id="{{ $document->id }}">
                                        <i class="fas fa-folder-open text-warning"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                    No documents in this folder.
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
                    @if ($documents->lastPage() > 1)
                       {{ $documents->onEachSide(2)->links('pagination::bootstrap-4') }}
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

<!-- Move Folder Modal -->
<div class="modal fade" id="moveFolderModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
 <form
    id="moveFolderForm"
    method="POST"
    action="{{ route('documents.move-folder') }}">

            @csrf

            <input type="hidden" id="document_id" name="document_id">

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Move Document</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">

                    <div class="form-group">
                        <label>Select Folder</label>

                        <select
                            class="form-control"
                            name="folder_id"
                            id="folder_id">

                            <option value="">Choose Folder</option>

                            @foreach($folderOptions as $folder)
                                <option value="{{ $folder['id'] }}">
                                    {{ $folder['name'] }}
                                </option>
                            @endforeach

                        </select>
                    </div>

                </div>

                <div class="modal-footer">
                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-dismiss="modal">
                        Cancel
                    </button>

                    <button
                        type="submit"
                        class="btn btn-primary">
                        Move
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="shareDocumentModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="shareForm"
              method="POST"
              action="{{ route('documents.share') }}">
            @csrf

            <input type="hidden"
                   name="document_id"
                   id="share_document_id">

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Share Document</h5>
                    <button class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">

                    <div id="userContainer">

                        <div class="user-row share-user-row d-flex mb-2">

                            <select class="form-control user-select"
                                    name="user_ids[]">
                                <option value="">Choose User</option>

                                @foreach($userOptions as $user)
                                    <option value="{{ $user->id }}">
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>

                            <button type="button"
                                    class="btn btn-danger ml-2 btn-remove-user"
                                    style="display:none">
                                <i class="fas fa-times"></i>
                            </button>

                        </div>

                    </div>

                    <button type="button"
                            class="btn btn-outline-primary btn-sm"
                            id="addUserBtn">
                        <i class="fas fa-plus"></i> Add User
                    </button>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary"
                            data-dismiss="modal"
                            type="button">
                        Cancel
                    </button>

                    <button class="btn btn-primary"
                            type="submit">
                        Share
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>

<script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf-lib/1.17.1/pdf-lib.min.js"></script>
<script>
$('#requesterSelect, #addresseeSelect').select2({
    theme: 'bootstrap4',
    placeholder: 'Search...',
    allowClear: true,
    width: '100%'
});

// Bulk Action Script
const selectAll = document.getElementById('selectAll');
const checkboxes = document.querySelectorAll('.rowCheckbox');
const bulkExportBtn = document.getElementById('bulkExportBtn');
const bulkApproveBtn = document.getElementById('bulkApproveBtn');

const filterForm = document.querySelector('.document-filter form');
let filterSearchTimer;
const submitFilter = () => {
    clearTimeout(filterSearchTimer);
    filterSearchTimer = setTimeout(() => {
        if (filterForm?.requestSubmit) filterForm.requestSubmit();
        else filterForm?.submit();
    }, 150);
};

filterForm?.querySelector('[name="search"]')?.addEventListener('input', function () {
    clearTimeout(filterSearchTimer);
    filterSearchTimer = setTimeout(submitFilter, 450);
});
$(filterForm).on('change', 'select, input[type="date"]', submitFilter);

$(document).ready(function () {

    $('.cancel-document-form').on('submit', async function (e) {
        e.preventDefault();

        const form = this;
        const documentName = $(form).find('button').data('document-name');
        const result = await Swal.fire({
            title: 'Withdraw document?',
            text: `"${documentName}" will no longer be available for approval.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, withdraw it'
        });

        if (result.isConfirmed) {
            form.submit();
        }
    });

    $('.btn-move-folder').on('click', function (e) {
        e.preventDefault();

        let documentId = $(this).data('document-id');

        $('#document_id').val(documentId);

        $('#moveFolderModal').modal('show');
    });

});

function toggleButtons() {
    const anyChecked = document.querySelectorAll('.rowCheckbox:checked').length > 0;
    bulkExportBtn.disabled = !anyChecked;
    bulkApproveBtn.disabled = !anyChecked;
}

// Select All
selectAll?.addEventListener('change', function () {
    checkboxes.forEach(cb => cb.checked = this.checked);
    toggleButtons();
});

// Individual checkbox
checkboxes.forEach(cb => {
    cb.addEventListener('change', toggleButtons);
});

{{-- Tambahkan di dalam <script> yang sudah ada --}}
// ==================== BULK EXPORT HANDLER ====================
bulkExportBtn?.addEventListener('click', async function () {
    const checkedBoxes = document.querySelectorAll('.rowCheckbox:checked');
    const documentIds = Array.from(checkedBoxes).map(cb => cb.value);

    if (documentIds.length === 0) return;

    const confirmResult = await Swal.fire({
        title: 'Export Documents',
        html: `You are about to export <strong>${documentIds.length}</strong> document(s) as ZIP file.`,
        icon: 'info',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, Export Now'
    });

    if (!confirmResult.isConfirmed) return;

    const originalText = this.innerHTML;
    this.disabled = true;
    this.innerHTML = `<i class="fas fa-spinner fa-spin"></i> Creating ZIP...`;

    try {
        const response = await fetch('{{ route("sent.bulkExport") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ document_ids: documentIds })
        });

        if (!response.ok) throw new Error('Export failed');

        // Handle file download
        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `documents_export_${new Date().toISOString().slice(0,10)}.zip`;
        document.body.appendChild(a);
        a.click();
        a.remove();
        window.URL.revokeObjectURL(url);

        Swal.fire({
            icon: 'success',
            title: 'Export Successful',
            text: `${documentIds.length} documents have been exported.`,
            timer: 2500,
            showConfirmButton: false
        });

    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Export Failed',
            text: 'Failed to create ZIP file. Please try again.'
        });
    } finally {
        this.disabled = false;
        this.innerHTML = originalText;
    }
});

let folderSearchTimer;
$('#folderSearchInput').on('keyup', function () {
    clearTimeout(folderSearchTimer);
    
    const value = $(this).val().trim();
    folderSearchTimer = setTimeout(function () {
        let url = new URL(window.location.href);
        
        if (value) {
            url.searchParams.set('folder_search', value);
        } else {
            url.searchParams.delete('folder_search');
        }
        
        // Reset folder page ke 1 saat search
        url.searchParams.delete('folder_page');
        
        window.location.href = url.toString();
    }, 500);
});

$(document).on('click', '.btn-share-document', function () {

    $('#share_document_id').val($(this).data('document-id'));

    $('#userContainer').html(`
        <div class="user-row share-user-row d-flex mb-2">

            <select class="form-control user-select" name="user_ids[]">

                <option value="">Choose User</option>

                @foreach($userOptions as $user)
                    <option value="{{ $user->id }}">
                        {{ $user->name }}
                    </option>
                @endforeach

            </select>

            <button type="button"
                    class="btn btn-danger ml-2 btn-remove-user"
                    style="display:none">
                <i class="fas fa-times"></i>
            </button>

        </div>
    `);

    initializeShareUserSelect($('#userContainer .user-select'));
    $('#shareDocumentModal').modal('show');
});

function initializeShareUserSelect(select) {
    $(select).select2({
        theme: 'bootstrap4',
        placeholder: 'Search user...',
        allowClear: true,
        width: '100%',
        dropdownParent: $('#shareDocumentModal')
    });
}

$('#addUserBtn').click(function () {
    const sourceSelect = $('#userContainer .user-select').first();

    sourceSelect.select2('destroy');
    const row = sourceSelect.closest('.user-row').clone();
    initializeShareUserSelect(sourceSelect);

    row.find('select').val('');
    row.find('.btn-remove-user').show();

    $('#userContainer').append(row);
    initializeShareUserSelect(row.find('.user-select'));
});

$(document).on('click', '.btn-remove-user', function () {
    $(this).closest('.user-row').remove();
});
$('#shareForm').submit(function (e) {

    let users = [];
    let duplicate = false;
    let empty = false;

    $('.user-select').each(function () {

        let value = $(this).val();

        if (!value) {
            empty = true;
            return false;
        }

        if (users.includes(value)) {
            duplicate = true;
            return false;
        }

        users.push(value);
    });

    if (empty) {
        e.preventDefault();

        Swal.fire({
            icon: 'warning',
            text: 'Please select all users.'
        });

        return;
    }

    if (duplicate) {
        e.preventDefault();

        Swal.fire({
            icon: 'warning',
            text: 'Duplicate users are not allowed.'
        });
    }
});
</script>
@endpush
@endsection