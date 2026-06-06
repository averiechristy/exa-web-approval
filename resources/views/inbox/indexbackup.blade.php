@extends('layouts.app')

@section('title', 'Inbox')

@section('content')

<style>
    .folder-sidebar {
        min-height: calc(100vh - 160px);
        max-height: calc(100vh - 160px);
        overflow-y: auto;
    }

    .folder-sidebar a {
        color: #4e73df;
        font-weight: 500;
    }

    .folder-sidebar a:hover {
        text-decoration: none;
    }

    .folder-item:hover {
        background-color: #f8f9fc;
        border-radius: 4px;
    }
</style>

<div class="container-fluid">

    <!-- Page Title -->
    <h1 class="h3 mb-4 text-gray-800">Inbox</h1>

    <div class="row">

        <!-- ===== FOLDER SIDEBAR ===== -->
        <div class="col-md-3 col-lg-2 mb-4">
            <div class="card shadow folder-sidebar">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Folders</h6>
                </div>

                <div class="card-body p-2">
                    <ul class="list-group list-group-flush">
                        @foreach($folders as $folder)
                            @include('components.folder-tree', ['folder' => $folder])
                        @endforeach

                        @if($folders->isEmpty())
                            <li class="list-group-item p-2 text-muted">
                                No folders available
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>

        <!-- ===== MAIN CONTENT ===== -->
        <div class="col-md-9 col-lg-10">

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

            <!-- Main Content Area -->
            @if(!isset($folder))
                <!-- Empty State -->
                <div class="card shadow mb-4">
                    <div class="card-body text-center py-16 text-gray-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-16 w-16 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2 2 2 0 01-2 2 2 2 0 01-2-2 2 2 0 012-2 2 2 0 01-2-2 2 2 0 012-2zm0 0V9a2 2 0 012-2" />
                        </svg>
                        <p class="text-xl font-medium text-gray-700">No folder selected</p>
                        <p class="mt-2">Please choose a folder from the sidebar to view documents that need your approval.</p>
                    </div>
                </div>

            @else
                <!-- Documents Table -->
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
                                            <br>
                                            <small class="text-muted">{{ $document->path ?? '-' }}</small>
                                        </td>

                                        <td>
                                            @if($document->status == 'DRAFT')
                                                <span class="badge badge-secondary">Draft</span>
                                            @elseif($document->status == 'IN_PROGRESS')
                                                <span class="badge badge-warning">Waiting Approval</span>
                                            @elseif($document->status == 'COMPLETED')
                                                <span class="badge badge-success">Completed</span>
                                            @elseif($document->status == 'REJECTED')
                                                <span class="badge badge-danger">Rejected</span>
                                            @endif
                                        </td>

                                        <td>{{ $document->updated_at->format('d M Y, H:i') }}</td>
                                        
                                        <td>{{ $document->requester->name ?? '-' }}</td>

                                        <td class="text-center">
                                            <a href="preview.html" class="btn btn-sm btn-light" title="View Detail">
                                                <i class="fas fa-eye text-secondary"></i>
                                            </a>
                                            <a href="#" class="btn btn-sm btn-light" title="Download">
                                                <i class="fas fa-download text-success"></i>
                                            </a>
                                            <a href="#" class="btn btn-sm btn-light" title="Share">
                                                <i class="fas fa-share-alt text-info"></i>
                                            </a>
                                            <a href="#" class="btn btn-sm btn-light" title="Move Folder">
                                                <i class="fas fa-folder-open text-warning"></i>
                                            </a>
                                            <a href="#" class="btn btn-sm btn-light" title="Void">
                                                <i class="fas fa-ban text-danger"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            No documents in this folder.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        @if($documents->hasPages())
                            <div class="mt-4">
                                {{ $documents->links() }}
                            </div>
                        @endif

                    </div>
                </div>
            @endif

        </div>
        <!-- END MAIN CONTENT -->

    </div>
</div>z

<style>
    .folder-sidebar {
        min-height: calc(100vh - 160px);
        max-height: calc(100vh - 160px);
        overflow-y: auto;
    }


    /* === ACTIVE STATE === */
 .folder-item {
    padding: 6px 8px;
    margin-bottom: 2px;
    transition: all 0.2s;
}

.folder-item:hover {
    background-color: #f8f9fc;
    border-radius: 4px;
}

.folder-item.active {
    background-color: #e0f0ff;
    border-left: 3px solid #2c7be5;
    border-radius: 4px;
}

.folder-item.active .folder-link {
    color: #2c7be5;
    font-weight: 600;
}
</style>

@endsection