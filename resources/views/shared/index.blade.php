@extends('layouts.app')
@section('title', 'Shared')

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

    /* Custom Override Select2 Bootstrap 4/SB Admin 2 */
.select2-container--bootstrap4 .select2-selection--single {
    height: 31px !important;
    min-height: 31px !important;
    padding: 0 !important;
    font-size: 0.875rem !important;
    font-weight: 400 !important;
    line-height: 1.2 !important;
    color: #6e707e !important;
    background-color: #fff !important;
    border: 1px solid #d1d3e2 !important;
    border-radius: .35rem !important;
    position: relative !important;
    display: flex !important;
    align-items: center !important;
    overflow: hidden !important;
}

/* Mengatur posisi teks placeholder & teks terpilih */
.select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
    display: flex !important;
    align-items: center !important;
    line-height: 1.2 !important;
    padding: 0 36px 0 12px !important;
    color: #6e707e !important;
    width: 100% !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    white-space: nowrap !important;
}

/* Mengatur warna teks placeholder saat belum ada yang dipilih */
.select2-container--bootstrap4 .select2-selection--single .select2-selection__placeholder {
    color: #858796 !important;
}

/* Mengatur panah dropdown (caret) agar presisi di tengah */
.select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow {
    height: 100% !important;
    top: 50% !important;
    right: 10px !important;
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

/* Style saat input fokus/diklik */
.select2-container--bootstrap4.select2-container--focus .select2-selection--single {
    border-color: #bac8f3 !important;
    box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25) !important;
}

</style>

@section('content')
<div class="container-fluid">
            <h1 class="h3 mb-4 text-gray-800">Shared Document</h1>

<!-- 
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif -->
    

    {{-- Documents Section - Hanya tampil jika sedang di dalam folder --}}
        <!-- Top Bar -->
        <div class="d-flex justify-content-between mb-3">


        </div>

            <!-- Filter -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('shared.index') }}">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label class="small font-weight-bold">Document Name</label>
                            <input type="text" id="searchInput" name="search" class="form-control form-control-sm" placeholder="Search document name..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="small font-weight-bold">Status</label>
                            <select class="form-control form-control-sm" name="status">
                                <option value="">All Status</option>
                                <option value="Need Approval" {{ request('status') == 'Need Approval' ? 'selected' : '' }}>Need Approval</option>
                                <option value="In Progress" {{ request('status') == 'In Progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="Approved" {{ request('status') == 'Approved' ? 'selected' : '' }}>Approved</option>
                                <option value="Rejected" {{ request('status') == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                        </div>
                            <div class="col-md-3">
    <label class="small font-weight-bold">Requester</label>
    <select class="form-control form-control-sm select2" id="requesterSelect" name="requester_id">
        <option value="">All Requesters</option>
        @foreach($userOptions as $user)
            <option value="{{ $user->id }}" {{ request('requester_id') == $user->id ? 'selected' : '' }}>
                {{ $user->name }} ({{ $user->username }})
            </option>
        @endforeach
    </select>
</div>
                        <div class="col-md-2">
                            <label class="small font-weight-bold">From</label>
                            <input type="date" class="form-control form-control-sm" name="from_date" value="{{ request('from_date') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="small font-weight-bold">To</label>
                            <input type="date" class="form-control form-control-sm" name="to_date" value="{{ request('to_date') }}">
                        </div>
                        <div class="col-12 text-right mt-3">
                            <div class="d-flex">
                                <button class="btn btn-primary btn-sm mr-1" type="submit">
                                    <i class="fas fa-filter"></i> Apply Filter
                                </button>
                                <a href="{{ route('shared.index') }}" class="btn btn-secondary btn-sm">
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
@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf-lib/1.17.1/pdf-lib.min.js"></script>
<script>
// Bulk Action Script
const selectAll = document.getElementById('selectAll');
const checkboxes = document.querySelectorAll('.rowCheckbox');
const bulkExportBtn = document.getElementById('bulkExportBtn');

let searchTimer;

$('#requesterSelect').select2({
        theme: 'bootstrap4',
        placeholder: 'Search requester...',
        allowClear: true,
        width: '100%'
    });
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
@endpush
@endsection
