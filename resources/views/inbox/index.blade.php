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
</style>

@section('content')
<div class="container-fluid">

    <!-- Header + Breadcrumb -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('inbox.index') }}">Inbox</a>
                    </li>
                    
                    @if(isset($folder) && $breadcrumb)
                        @foreach($breadcrumb as $crumb)
                            @if($loop->last)
                                <li class="breadcrumb-item active">{{ $crumb->folder_name }}</li>
                            @else
                                <li class="breadcrumb-item">
                                    <a href="{{ route('inbox.show', $crumb->id) }}">{{ $crumb->folder_name }}</a>
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
        <h6 class="text-muted mb-3">
            @if(isset($folder))
                Folders in <strong>"{{ $folder->folder_name }}"</strong>
            @else
                Root Folders
            @endif
        </h6>

        @if($folders->isNotEmpty())
            <div class="row g-3">
                @foreach($folders as $f)
                <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                    <a href="{{ route('inbox.show', $f->id) }}" class="text-decoration-none">
                        <div class="text-center p-3 border rounded-3 hover-shadow bg-white 
                            {{ isset($folder) && $folder->id == $f->id ? 'current-folder' : '' }}">
                            <i class="fas fa-folder fa-3x text-primary mb-2"></i>
                            <p class="mb-1 fw-medium text-dark">{{ $f->folder_name }}</p>
                        </div>
                    </a>
                </div>
                @endforeach
            </div>
        @else
            <p class="text-muted">No folders available.</p>
        @endif
    </div>

    {{-- Documents Section - Hanya tampil jika sedang di dalam folder --}}
    @if(isset($folder))
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

            <input type="text" class="form-control w-25" placeholder="Search document...">
        </div>

        <!-- Filter -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="row align-items-end">
                    <div class="col-md-3">
                        <label class="small font-weight-bold">Status</label>
                        <select class="form-control">
                            <option value="">All Status</option>
                            <option value="waiting">Waiting Approval</option>
                            <option value="signed">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="small font-weight-bold">From</label>
                        <input type="date" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="small font-weight-bold">To</label>
                        <input type="date" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-primary btn-block">
                            <i class="fas fa-filter"></i> Apply Filter
                        </button>
                    </div>
                </div>
            </div>
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
                                    <br>
                                    <small class="text-muted">{{ $document->path ?? '-' }}</small>
                                </td>
                                <td>
                                    
                                        <span class="badge badge-warning">{{$document->status}}</span>
                                </td>
                                <td>{{ $document->updated_at->format('d M Y, H:i') }}</td>
                                <td>{{ $document->requester?->name ?? '-' }}</td>
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
                                    <a href="#" class="btn btn-sm btn-light" title="Share">
                                        <i class="fas fa-share-alt text-info"></i>
                                    </a>
                                    <a href="#" class="btn btn-sm btn-light" title="Move">
                                        <i class="fas fa-folder-open text-warning"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                    No documents in this folder.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($documents->hasPages())
                    <div class="mt-4">
                        {{ $documents->links() }}
                    </div>
                @endif
            </div>
        </div>
    @endif

</div>
<script>
// Bulk Action Script
const selectAll = document.getElementById('selectAll');
const checkboxes = document.querySelectorAll('.rowCheckbox');
const bulkExportBtn = document.getElementById('bulkExportBtn');
const bulkApproveBtn = document.getElementById('bulkApproveBtn');

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
bulkApproveBtn?.addEventListener('click', async function () {
    const checkedBoxes = document.querySelectorAll('.rowCheckbox:checked');
    const documentIds = Array.from(checkedBoxes).map(cb => cb.value);

    if (documentIds.length === 0) return;

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

    if (!confirmResult.isConfirmed) {
        return;
    }

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
            Swal.fire({
                icon: 'error',
                title: 'Bulk Approve Failed',
                html: result.error || result.message,
                footer: result.invalid_documents ? 
                    '<small>Invalid documents:<br>' + result.invalid_documents.join('<br>') + '</small>' : ''
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
</script>
@endsection