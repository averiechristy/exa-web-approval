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
    .current-folder {
        background-color: #f8f9fc;
        border-left: 4px solid #4e73df;
    }
    .folder-card{
    min-height: 150px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    transition: all .2s ease;
}

.folder-card:hover{
    box-shadow: 0 4px 12px rgba(0,0,0,.12);
    transform: translateY(-2px);
}

.folder-name{
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
    width: 100%;
}
</style>

@section('content')
<div class="container-fluid">

    <!-- Header + Breadcrumb -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('mydoc.index') }}">My Document</a>
                    </li>
                    
                    @if(isset($folder) && $breadcrumb)
                        @foreach($breadcrumb as $crumb)
                            @if($loop->last)
                                <li class="breadcrumb-item active">{{ $crumb->folder_name }}</li>
                            @else
                                <li class="breadcrumb-item">
                                    <a href="{{ route('mydoc.show', $crumb->id) }}">{{ $crumb->folder_name }}</a>
                                </li>
                            @endif
                        @endforeach
                    @endif
                </ol>
            </nav>
        </div>
    </div>

<!-- Folders Section -->
<div class="mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="text-muted mb-0">
            @if(isset($folder))
                Folders in <strong>"{{ $folder->folder_name }}"</strong>
            @else
                Root Folders
            @endif
        </h6>

        <!-- Search Folder -->
        <form method="GET" 
              action="{{ isset($folder) ? route('mydoc.show', $folder) : route('mydoc.index') }}" 
              style="width: 260px;">
            
            <div class="input-group input-group-sm">
                <input 
                    type="text" 
                    name="folder_search"
                    id="folderSearchInput"
                    class="form-control"
                    placeholder="Search folders..."
                    value="{{ request('folder_search') }}">
                
                <button class="btn btn-outline-secondary" type="submit">
                    <i class="fas fa-search"></i>
                </button>
                
                @if(request('folder_search'))
                    <a href="{{ isset($folder) ? route('mydoc.show', $folder) : route('mydoc.index') }}" 
                       class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    @if($folders->isNotEmpty())
<div class="row">
    @foreach($folders as $f)
        <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-4">
            <a href="{{ route('mydoc.show', $f->id) }}" class="text-decoration-none d-block h-100">
                <div class="folder-card border rounded-3 bg-white text-center p-3 h-100">
                    <i class="fas fa-folder fa-3x text-primary mb-3"></i>

                    <p class="folder-name mb-0 text-dark fw-medium"
                       data-bs-toggle="tooltip"
                       data-bs-placement="top"
                       title="{{ $f->folder_name }}">
                        {{ $f->folder_name }}
                    </p>
                </div>
            </a>
        </div>
    @endforeach
</div>


        <!-- Pagination -->
<!-- Pagination Folder -->
<div class="d-flex justify-content-between align-items-center mt-5 mb-5">
    <div class="text-muted small">
        Showing <strong>{{ $folders->firstItem() }}</strong> to 
        <strong>{{ $folders->lastItem() }}</strong> 
        of <strong>{{ $folders->total() }}</strong> folders
    </div>
    
    @if($folders->lastPage() > 1)
        {{ $folders->onEachSide(2)->links('pagination::bootstrap-4', ['pageName' => 'folder_page']) }}
    @endif
</div>
    @else
        <p class="text-muted">No folders found.</p>
    @endif
</div>
    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

    {{-- Documents Section - Hanya tampil jika sedang di dalam folder --}}
    @if(isset($folder))
        <!-- Top Bar -->
        <div class="d-flex justify-content-between mb-3">
            <div>
                <button class="btn btn-success mr-2" id="bulkExportBtn" disabled>
                    <i class="fas fa-file-export"></i> Export
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
        
        <!-- Documents -->
        <div class="card shadow mb-4">
            <div class="card-header bg-white">
                <h6 class="mb-0">Documents in "{{ $folder->folder_name }}"</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="bg-light">
                            <tr>
                                <th width="40"><input type="checkbox" id="selectAll"></th>
                                <th>Document Name</th>
                                <th>Status</th>
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
                                <td>{{ $document->updated_at->format('d M Y, H:i') }}</td>
                                <td>
                                    {{ $document->requester?->name ?? '-' }} 
                                    ({{ $document->requester?->username ?? '-' }})
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('mydoc.preview', $document->id) }}" class="btn btn-sm btn-light" title="View">
                                        <i class="fas fa-eye text-secondary"></i>
                                    </a>
                                    <a href="{{ route('mydoc.download', $document->id) }}" 
                                        class="btn btn-sm btn-light" 
                                        title="Download"
                                        download>
                                            <i class="fas fa-download text-success"></i>
                                        </a>

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
    @endif

</div>

<script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
<script>
// Bulk Action Script
const selectAll = document.getElementById('selectAll');
const checkboxes = document.querySelectorAll('.rowCheckbox');
const bulkExportBtn = document.getElementById('bulkExportBtn');

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
        const response = await fetch('{{ route("mydoc.bulkExport") }}', {
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

document.addEventListener('DOMContentLoaded', function () {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));

    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endsection