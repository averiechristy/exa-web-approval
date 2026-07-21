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
</style>

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Sent</h1>

    @if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

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

            <!-- Search Input -->
            <input 
                type="text" 
                id="searchInput"
                name="search"
                class="form-control"
                placeholder="Search document name..."
                value="{{ request('search') }}"
                style="width: 280px;"
            >
        </div>
    <!-- Filter -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('sent.index') }}">
                    <div class="row align-items-end">
                        <div class="col-md-2">
                            <label class="small font-weight-bold">Status</label>
                            <select class="form-control" name="status">
                                <option value="">All Status</option>
                                <option value="Need Approval" {{ request('status') == 'Need Approval' ? 'selected' : '' }}>Need Approval</option>
                                <option value="In Progress" {{ request('status') == 'In Progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="Approved" {{ request('status') == 'Approved' ? 'selected' : '' }}>Approved</option>
                                <option value="Rejected" {{ request('status') == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="small font-weight-bold">From</label>
                            <input type="date" class="form-control" name="from_date" value="{{ request('from_date') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="small font-weight-bold">To</label>
                            <input type="date" class="form-control" name="to_date" value="{{ request('to_date') }}">
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex gap-2">
                                <button class="btn btn-primary" type="submit" style="margin-right: 10px;">
                                    <i class="fas fa-filter"></i> Apply Filter
                                </button>
                                <a href="{{ route('sent.index') }}" 
                                class="btn btn-secondary flex-fill">
                                    <i class="fas fa-undo"></i> Reset
                                </a>
                            </div>
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
<button
    type="button"
    class="btn btn-sm btn-light btn-share-document"
    data-document-id="{{ $document->id }}"
    title="Share">
    <i class="fas fa-share-alt text-info"></i>
</button>
                                    <button type="button"
                                            class="btn btn-sm btn-light btn-move-folder"
                                            data-document-id="{{ $document->id }}">
                                        <i class="fas fa-folder-open text-warning"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
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

                        <div class="user-row d-flex mb-2">

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
<script>
// Bulk Action Script
const selectAll = document.getElementById('selectAll');
const checkboxes = document.querySelectorAll('.rowCheckbox');
const bulkExportBtn = document.getElementById('bulkExportBtn');
const bulkApproveBtn = document.getElementById('bulkApproveBtn');

let searchTimer;
$('#searchInput').on('keyup', function () {
    clearTimeout(searchTimer);

    let value = $(this).val().trim();

    searchTimer = setTimeout(function () {
        let url = new URL(window.location.href);

        if (value) {
            url.searchParams.set('search', value);
        } else {
            url.searchParams.delete('search');
        }

        // Preserve other parameters (perPage, status, requester_id, dll)
        const paramsToKeep = ['status', 'requester_id', 'from_date', 'to_date', 'perPage'];
        paramsToKeep.forEach(param => {
            let val = url.searchParams.get(param);
            if (!val) {
                const currentVal = new URLSearchParams(window.location.search).get(param);
                if (currentVal) url.searchParams.set(param, currentVal);
            }
        });

        window.location.href = url.toString();
    }, 450); // delay 450ms
});

$(document).ready(function () {

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

$('.btn-share-document').click(function () {

    $('#share_document_id').val($(this).data('document-id'));

    $('#userContainer').html(`
        <div class="user-row d-flex mb-2">

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

    $('#shareDocumentModal').modal('show');
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
@endsection