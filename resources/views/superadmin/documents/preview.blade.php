@extends('layouts.app')

@section('title', 'View Document')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('superadmin.documents.index') }}" class="btn btn-light btn-sm mb-2"><i class="fas fa-arrow-left"></i> Back</a>
            <h4 class="mb-0">{{ $document->document_name }}</h4>
        </div>
        @if($document->status !== 'Cancelled')
            <form method="POST" action="{{ route('superadmin.documents.void', $document) }}" class="void-document-form">
                @csrf
                <button type="submit" class="btn btn-danger"><i class="fas fa-ban"></i> Void</button>
            </form>
        @endif
    </div>

    <div class="card shadow">
        <div class="card-body p-2">
            @if($document->path)
                <iframe src="{{ asset('storage/' . $document->path) }}#toolbar=0&navpanes=0" style="width: 100%; height: 75vh; border: 0;" title="{{ $document->document_name }}"></iframe>
            @else
                <div class="text-center text-muted p-5">Document file not found.</div>
            @endif
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
</script>
@endpush