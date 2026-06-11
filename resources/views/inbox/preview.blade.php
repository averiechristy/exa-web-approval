@extends('layouts.app')
@section('title', 'Preview - ' . $document->document_name)

<style>
    .preview-container {
        background: #f8f9fc;
        border: 1px solid #e3e6f0;
        border-radius: 8px;
        min-height: 620px;
    }
    .document-frame {
        background: white;
        border: 1px solid #ddd;
        border-radius: 6px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    .pdf-iframe {
        width: 100%;
        height: 580px;
        border: none;
    }
    .status-badge {
        font-size: 0.95rem;
        padding: 8px 16px;
    }
</style>

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('inbox.show', $folder->id ?? '') }}" class="btn btn-light btn-sm mb-2">
                <i class="fas fa-arrow-left"></i> Back
            </a>
            <h4 class="mb-0">{{ $document->document_name }}</h4>
        </div>
        
        <div>
            @if(optional($document->documentApprovals->first())->status == 'PENDING')
                <button class="btn btn-success btn-lg px-4" id="approveBtn">
                    <i class="fas fa-check-circle"></i> Approve
                </button>
                <button class="btn btn-danger btn-lg px-4 ml-2" id="rejectBtn">
                    <i class="fas fa-times-circle"></i> Reject
                </button>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Document Preview</h6>
                    <div>
                        <a href="{{ asset('storage/' . $document->path) }}" class="btn btn-sm btn-light" target="_blank">
                            <i class="fas fa-download"></i> Download
                        </a>
                        <!-- <a href="#" class="btn btn-sm btn-light ml-2" onclick="window.print()">
                            <i class="fas fa-print"></i> Print
                        </a> -->
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="preview-container">
                        <div class="document-frame">
                            @if($document->path)
                              <iframe 
                                src="{{ asset('storage/' . $document->path) }}#toolbar=0&navpanes=0&scrollbar=0"
                                class="pdf-iframe"
                                title="{{ $document->document_name }}">
                            </iframe>
                            @else
                                <div class="h-100 d-flex flex-column align-items-center justify-content-center text-muted" style="height: 580px;">
                                    <i class="fas fa-file-pdf fa-5x mb-4 text-danger"></i>
                                    <h5 class="text-dark">PDF not found (404)</h5>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Document Information</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless small">
                        <tr>
                            <td width="40%"><strong>Status</strong></td>
                            <td><span class="badge badge-warning status-badge">{{$document->status}}</span></td>
                        </tr>
                        <tr>
                            <td><strong>Document Name</strong></td>
                            <td>{{ $document->document_name }}</td>
                        </tr>
                        <tr>
                            <td><strong>Created At</strong></td>
                            <td>{{ $document->created_at->format('d M Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Approve with SweetAlert
    document.getElementById('approveBtn')?.addEventListener('click', function() {
        Swal.fire({
            title: 'Approve Document?',
            text: "Are you sure you want to approve this document?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, Approve'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch("{{ route('document.approve', $document->id) }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Approved!',
                            text: 'Document has been approved successfully.',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                });
            }
        });
    });

    // Reject with SweetAlert
    document.getElementById('rejectBtn')?.addEventListener('click', function() {
        Swal.fire({
            title: 'Reject Document?',
            input: 'textarea',
            inputLabel: 'Rejection Reason',
            inputPlaceholder: 'Please explain the reason for rejecting this document...',
            inputAttributes: {
                'aria-label': 'Rejection reason'
            },
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Reject',
            cancelButtonText: 'Cancel',
            inputValidator: (value) => {
                if (!value) {
                    return 'A rejection reason is required.';
                }
            }
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                fetch("{{ route('document.reject', $document->id) }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        reason: result.value
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Rejected!',
                            text: 'The document has been rejected.',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                })
                .catch(error => {
                    Swal.fire('Error', 'An error occurred while processing the document rejection.', 'error');
                });
            }
        });
    });
</script>
@endsection