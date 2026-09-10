@extends('layouts.app')

@section('title', 'Document Management')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Document Management</h1>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('superadmin.documents.index') }}" class="form-row align-items-end mb-4">
                <div class="col-md-5 mb-2">
                    <label for="search">Document Name</label>
                    <input id="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search documents...">
                </div>
                <div class="col-md-3 mb-2">
                    <label for="status">Status</label>
                    <select id="status" name="status" class="form-control">
                        <option value="">All statuses</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <button class="btn btn-primary btn-block" type="submit"><i class="fas fa-filter"></i> Filter</button>
                </div>
                <div class="col-md-2 mb-2">
                    <a class="btn btn-secondary btn-block" href="{{ route('superadmin.documents.index') }}">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="bg-light">
                        <tr>
                            <th>Document Name</th>
                            <th>Status</th>
                            <th>Requester</th>
                            <th>Folder</th>
                            <th>Last Updated</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($documents as $document)
                            <tr>
                                <td>{{ $document->document_name }}</td>
                                <td><span class="badge badge-{{ $document->status === 'Approved' ? 'success' : ($document->status === 'Cancelled' ? 'dark' : 'secondary') }}">{{ $document->status }}</span></td>
                                <td>{{ $document->requester?->name ?? '-' }}</td>
                                <td>{{ $document->folder?->folder_name ?? '-' }}</td>
                                <td>{{ $document->updated_at?->format('d M Y, H:i') }}</td>
                                <td class="text-center">
                                    <a href="{{ route('superadmin.documents.preview', $document) }}" class="btn btn-sm btn-light" title="View"><i class="fas fa-eye text-secondary"></i></a>
                                    @if($document->status !== 'Cancelled')
                                        <form method="POST" action="{{ route('superadmin.documents.void', $document) }}" class="d-inline void-document-form">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-light" title="Void"><i class="fas fa-ban text-danger"></i></button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">No documents found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $documents->links() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.void-document-form').forEach(form => {
        form.addEventListener('submit', async event => {
            event.preventDefault();

            const result = await Swal.fire({
                title: 'Void document?',
                text: 'A voided document cannot be processed again.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, void it',
                cancelButtonText: 'Cancel'
            });

            if (result.isConfirmed) form.submit();
        });
    });

    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: @json(session('success')),
            timer: 2500,
            showConfirmButton: false
        });
    @elseif(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: @json(session('error'))
        });
    @endif
</script>
@endpush