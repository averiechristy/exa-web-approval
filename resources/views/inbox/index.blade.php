@extends('layouts.app')
@section('title', 'Inbox')

<style>
    .hover-shadow {
        transition: all 0.25s ease;
    }
    .hover-shadow:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.08) !important;
    }

    .hover-shadow {
        transition: all 0.25s ease;
    }
    .hover-shadow:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.08) !important;
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

.select2-container--bootstrap4 .select2-selection__clear {
    color: #111827 !important;
    font-size: 18px !important;
    right: 28px !important;
    top: 50% !important;
    transform: translateY(-50%) !important;
    margin: 0 !important;
    line-height: 1 !important;
}

</style>

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Inbox</h1>

    <!-- @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif -->

    {{-- Documents Section - Hanya tampil jika sedang di dalam folder --}}
 
        <!-- Top Bar -->
        <div class="d-flex justify-content-between mb-3">
            <div>
                <button class="btn btn-success mr-2" id="bulkExportBtn" disabled>
                    <i class="fas fa-file-export"></i> Export
                </button>
                <button class="btn btn-primary" id="bulkApproveBtn" disabled>
                    <i class="fas fa-check-circle"></i> Approve
                </button>
            </div>
        </div>
    <!-- Filter -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('inbox.index') }}">
            <div class="row g-2 align-items-end">
                <!-- Document Name -->
                <div class="col-lg-3 col-md-4">
                    <label class="form-label small font-weight-bold text-muted mb-1">Document Name</label>
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search document name..." value="{{ request('search') }}">
                </div>

                <!-- Status -->
                <div class="col-lg-2 col-md-4">
                    <label class="form-label small font-weight-bold text-muted mb-1">Status</label>
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
                    <label class="form-label small font-weight-bold text-muted mb-1">Requester</label>
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
                <div class="col-lg-3 col-md-4">
                    <label class="form-label small font-weight-bold text-muted mb-1">Addressee</label>
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
                <div class="col-lg-2 col-md-3 col-6">
                    <label class="form-label small font-weight-bold text-muted mb-1">From</label>
                    <input type="date" class="form-control form-control-sm" name="from_date" value="{{ request('from_date') }}">
                </div>

                <!-- To Date -->
                <div class="col-lg-2 col-md-3 col-6">
                    <label class="form-label small font-weight-bold text-muted mb-1">To</label>
                    <input type="date" class="form-control form-control-sm" name="to_date" value="{{ request('to_date') }}">
                </div>

                <!-- Action Buttons -->
                <div class="col-12 text-end mt-3">
                    <button class="btn btn-primary btn-sm me-1" type="submit">
                        <i class="fas fa-filter me-1"></i> Apply Filter
                    </button>
                    <a href="{{ route('inbox.index') }}" class="btn btn-secondary btn-sm">
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
            <a href="{{ route('inbox.preview', $document->id) }}" class="btn btn-sm btn-light" title="View">
                <i class="fas fa-eye text-secondary"></i>
            </a>
            
            <a href="{{ route('inbox.download', $document->id) }}" 
               class="btn btn-sm btn-light" 
               title="Download"
               download>
                <i class="fas fa-download text-success"></i>
            </a>

            {{-- Tombol share hanya tampil jika status Approved --}}
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

<!-- Share Document Modal -->
<div class="modal fade" id="shareDocumentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form method="POST" action="{{ route('documents.share') }}" id="shareForm">
            @csrf

            <input type="hidden" name="document_id" id="share_document_id">

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Share Document</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">

                    <div class="form-group">
                        <label>Select User</label>

<div id="userContainer">
    <div class="user-row mb-2 d-flex">
        <select class="form-control user-select" name="user_ids[]" required>
            <option value="">Choose User</option>
            @foreach($userOptions as $user)
                <option value="{{ $user->id }}">
                    {{ $user->name }} ({{ $user->username }})
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

<button type="button" class="btn btn-outline-primary mt-2" id="addUserBtn">
    <i class="fas fa-plus"></i> Add User
</button>
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
// Bulk Action Script
const selectAll = document.getElementById('selectAll');
const checkboxes = document.querySelectorAll('.rowCheckbox');
const bulkExportBtn = document.getElementById('bulkExportBtn');
const bulkApproveBtn = document.getElementById('bulkApproveBtn');

let searchTimer;

$('#requesterSelect, #addresseeSelect').select2({
        theme: 'bootstrap4',
        placeholder: 'Search...',
        allowClear: true,
        width: '100%'
    });
$(document).ready(function () {

    $('.btn-move-folder').on('click', function (e) {
        e.preventDefault();

        let documentId = $(this).data('document-id');

        $('#document_id').val(documentId);

        $('#moveFolderModal').modal('show');
    });

});

$('#addUserBtn').click(function () {

    let row = $('.user-row:first').clone();

    row.find('select').val('');

    row.find('.btn-remove-user').show();

    $('#userContainer').append(row);
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
            title: 'Validation',
            text: 'Please select all users.'
        });

        return;
    }

    if (duplicate) {
        e.preventDefault();

        Swal.fire({
            icon: 'warning',
            title: 'Validation',
            text: 'Duplicate users are not allowed.'
        });

        return;
    }
});

$('.btn-share-document').on('click', function (e) {
    e.preventDefault();

    let documentId = $(this).data('document-id');

    $('#share_document_id').val(documentId);

    $('#shareDocumentModal').modal('show');
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
// ==================== BULK APPROVE HANDLER ====================
// ==================== BULK APPROVE HANDLER ====================
bulkApproveBtn?.addEventListener('click', async function () {
    const checkedBoxes = document.querySelectorAll('.rowCheckbox:checked');
    if (checkedBoxes.length === 0) return;

    let invalidStatusList = [];
    const documentIds = [];

    // Filter status di sisi client sebelum mengirim request
    checkedBoxes.forEach(cb => {
        const row = cb.closest('tr');
        const docName = row.querySelector('td:nth-child(2)').innerText.trim();
        const statusBadge = row.querySelector('td:nth-child(3) .badge').innerText.trim();

        if (statusBadge === 'Approved' || statusBadge === 'Rejected') {
            invalidStatusList.push(`<li><b>${docName}</b> (Status: ${statusBadge})</li>`);
        } else {
            documentIds.push(cb.value);
        }
    });

    // Jika ada dokumen yang statusnya sudah Approved atau Rejected
    if (invalidStatusList.length > 0) {
        Swal.fire({
            icon: 'error',
            title: 'Bulk Approve Failed',
            html: `
                <div class="text-left">
                    <p class="text-danger font-weight-bold mb-2">
                        Cannot process approval for documents with the following status:
                    </p>
                    <ul class="text-muted small pl-3 mb-3">
                        ${invalidStatusList.join('')}
                    </ul>
                    <small class="text-muted">
                        Only documents with status <b>Need Approval</b> or <b>In Progress</b> can be approved.
                    </small>
                </div>
            `,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Understand'
        });
        return; // Hentikan eksekusi, tidak ada AJAX call yang dikirim
    }

    // Konfirmasi Konfirmasi Approval
    const confirmResult = await Swal.fire({
        title: 'Are you sure?',
        html: `You are about to approve <strong>${documentIds.length}</strong> document(s).<br><br>
               <small class="text-muted">I have opened and reviewed all selected documents.<br>
               If any document has not been opened yet, the entire bulk approve will be cancelled.</small>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, Approve All',
        cancelButtonText: 'Cancel'
    });

    if (!confirmResult.isConfirmed) return;

    const originalText = this.innerHTML;
    this.disabled = true;
    this.innerHTML = `<i class="fas fa-spinner fa-spin"></i> Processing...`;

    try {
        const response = await fetch('{{ route("inbox.bulkApprove") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ document_ids: documentIds })
        });

        const result = await response.json();

        if (result.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: result.message,
                timer: 2500,
                showConfirmButton: false
            }).then(() => location.reload());
        } else {
            // Penanganan error detail jika diprotes backend
            let errorMessage = result.error || result.message || 'Operation failed.';
            
            // Format daftar error khusus jika mengembalikan invalid_documents dari Controller
            let formattedInvalidDocs = '';
            if (result.invalid_documents && result.invalid_documents.length > 0) {
                formattedInvalidDocs = `
                    <div class="text-left mt-3">
                        <p class="text-danger mb-1 font-weight-bold small">Reason for failure:</p>
                        <ul class="small text-muted pl-3 mb-0">
                            ${result.invalid_documents.map(doc => `<li>${doc}</li>`).join('')}
                        </ul>
                    </div>`;
            }

            Swal.fire({
                icon: 'error',
                title: 'Bulk Approve Failed',
                html: `<div>${errorMessage}</div>${formattedInvalidDocs}`,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Close'
            });
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Connection error. Please try again.'
        });
    } finally {
        this.disabled = false;
        this.innerHTML = originalText;
    }
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
        const response = await fetch('{{ route("inbox.bulkExport") }}', {
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
</script>
@endpush
@endsection