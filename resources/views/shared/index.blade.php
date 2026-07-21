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
            <h1 class="h3 mb-4 text-gray-800">Shared Document</h1>


    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    

    {{-- Documents Section - Hanya tampil jika sedang di dalam folder --}}
        <!-- Top Bar -->
        <div class="d-flex justify-content-between mb-3">


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
                <form method="GET" action="{{ route('shared.index') }}">
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
                        <div class="col-md-3">
                            <label class="small font-weight-bold">Requester</label>
                            <select class="form-control" name="requester_id">
                                <option value="">All Requesters</option>
                                @foreach($userOptions as $user)
                                    <option value="{{ $user->id }}" {{ request('requester_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
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
                                <a href="{{ route('shared.index') }}" 
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
                                    
                                        <span class="badge badge-warning">{{$document->status}}</span>
                                </td>
                                <td>{{ $document->updated_at->format('d M Y, H:i') }}</td>
                                <td>
                                    {{ $document->requester?->name ?? '-' }} 
                                    ({{ $document->requester?->username ?? '-' }})
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('shared.preview', $document->id) }}" class="btn btn-sm btn-light" title="View">
                                        <i class="fas fa-eye text-secondary"></i>
                                    </a>
                                    <a href="{{ route('shared.download', $document->id) }}" 
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

                @if($documents->hasPages())
                    <div class="mt-4">
                        {{ $documents->links() }}
                    </div>
                @endif
            </div>
        </div>
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
// ==================== BULK APPROVE HANDLER ====================

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
        const response = await fetch('{{ route("shared.bulkExport") }}', {
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
@endsection
