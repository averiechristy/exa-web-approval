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
                    <a href="{{ route('shared.show', $f->id) }}" class="text-decoration-none">
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
                                    
                                        <span class="badge badge-warning">{{$document->status}}</span>
                                </td>
                                <td>{{ $document->updated_at->format('d M Y, H:i') }}</td>
                                <td>
                                    {{ $document->requester?->name ?? '-' }} 
                                    ({{ $document->requester?->username ?? '-' }})
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
                                    <a href="#" class="btn btn-sm btn-light" title="Share">
                                        <i class="fas fa-share-alt text-info"></i>
                                    </a>
                                    <button type="button"
                                            class="btn btn-sm btn-light btn-move-folder"
                                            data-document-id="{{ $document->id }}">
                                        <i class="fas fa-folder-open text-warning"></i>
                                    </button>
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




@endsection