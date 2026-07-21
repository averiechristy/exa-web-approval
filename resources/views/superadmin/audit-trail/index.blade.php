@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800">System Activity History</h1>
    <p class="mb-4">Track user activities, document updates, and data changes across the platform.</p>

    <!-- Filter Cards -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Search & Filter</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('audit-trail.index') }}" method="GET" class="row">
                <div class="col-md-3 mb-3">
                    <label class="small font-weight-bold text-gray-600">Module / Feature</label>
                    <select name="log_name" class="form-control form-control-sm">
                        <option value="">-- All Modules --</option>
                        @foreach($logNames as $name)
                            <option value="{{ $name }}" {{ request('log_name') == $name ? 'selected' : '' }}>{{ ucfirst($name) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="small font-weight-bold text-gray-600">Action Type</label>
                    <input type="text" name="event" value="{{ request('event') }}" placeholder="e.g. document.rejected" class="form-control form-control-sm">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="small font-weight-bold text-gray-600">Keyword Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by document name, org, or description..." class="form-control form-control-sm">
                </div>
                <div class="col-md-2 mb-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm btn-block"><i class="fas fa-filter fa-sm"></i> Apply Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th>Date & Time</th>
                            <th>Performed By</th>
                            <th>Module</th>
                            <th>Action</th>
                            <th>Description</th>
                            <th class="text-center">Options</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activities as $activity)
                            <tr>
                                <td class="small">{{ $activity->created_at->format('d M Y H:i:s') }}</td>
                                <td><strong>{{ $activity->causer->name ?? 'System Automated' }}</strong></td>
                                <td><span class="badge badge-secondary px-2 py-1">{{ $activity->log_name }}</span></td>
                                <td><code class="text-primary font-weight-bold">{{ $activity->event }}</code></td>
                                <td>{{ $activity->description }}</td>
                                <td class="text-center">
                                    <button class="btn btn-info btn-xs btn-sm view-details" 
                                            data-id="{{ $activity->id }}"
                                            data-actor="{{ $activity->causer->name ?? 'System Automated' }}"
                                            data-event="{{ $activity->event }}"
                                            data-payload="{{ json_encode($activity->properties) }}">
                                        <i class="fas fa-eye fa-sm"></i> View Details
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No activity records found matching your filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3 float-right">
                {{ $activities->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Details Modal -->
<div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-gray-900 text-white">
                <h5 class="modal-title" id="detailModalLabel">Activity Details Log</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="modalContentContainer">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>User:</strong> <span id="modal-actor"></span>
                    </div>
                    <div class="col-md-6">
                        <strong>Action Taken:</strong> <code id="modal-event" class="text-primary font-weight-bold"></code>
                    </div>
                </div>
                
                <hr>
                
                <h6>Current Data Snapshot:</h6>
                <div class="table-responsive mb-3">
                    <table class="table table-sm table-bordered bg-light">
                        <thead>
                            <tr class="table-secondary">
                                <th>Data Field</th>
                                <th>Value</th>
                            </tr>
                        </thead>
                        <tbody id="modal-attributes-body">
                            <!-- Populated via JavaScript -->
                        </tbody>
                    </table>
                </div>

                <div id="modal-changes-section" style="display: none;">
                    <h6>Data Changes (Before vs After):</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered bg-light">
                            <thead>
                                <tr class="table-warning">
                                    <th>Data Field</th>
                                    <th>Old Value (Before)</th>
                                    <th>New Value (After)</th>
                                </tr>
                            </thead>
                            <tbody id="modal-changes-body">
                                <!-- Populated via JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.body.addEventListener('click', function (event) {
            const button = event.target.closest('.view-details');
            
            if (button) {
                event.preventDefault();
                
                const actor = button.getAttribute('data-actor');
                const eventName = button.getAttribute('data-event');
                const rawPayload = button.getAttribute('data-payload');
                
                let payload = {};
                try {
                    payload = JSON.parse(rawPayload) || {};
                } catch (e) {
                    console.error("Failed to parse activity data", e);
                }

                document.getElementById('modal-actor').textContent = actor;
                document.getElementById('modal-event').textContent = eventName;

                // Process Snapshot Attributes
                const attributesBody = document.getElementById('modal-attributes-body');
                attributesBody.innerHTML = ''; 
                
                const attributes = payload.attributes || payload || {}; 
                
                let hasAttributes = false;
                for (const [key, value] of Object.entries(attributes)) {
                    if (key === 'changes' || key === 'old') continue;
                    
                    hasAttributes = true;
                    const displayValue = typeof value === 'object' ? JSON.stringify(value) : value;
                    attributesBody.insertAdjacentHTML('beforeend', `
                        <tr>
                            <td class="font-weight-bold text-gray-700" width="30%">${key}</td>
                            <td>${displayValue ?? '<span class="text-muted">empty (null)</span>'}</td>
                        </tr>
                    `);
                }
                
                if (!hasAttributes) {
                    attributesBody.innerHTML = `<tr><td colspan="2" class="text-center text-muted">No snapshot data recorded.</td></tr>`;
                }

                // Process Changes
                const changesSection = document.getElementById('modal-changes-section');
                const changesBody = document.getElementById('modal-changes-body');
                changesBody.innerHTML = ''; 
                
                const oldValues = payload.old || {};
                const newValues = payload.attributes || {};
                
                let hasChanges = false;
                
                for (const [key, oldVal] of Object.entries(oldValues)) {
                    hasChanges = true;
                    const newVal = newValues[key];
                    
                    const displayOld = typeof oldVal === 'object' ? JSON.stringify(oldVal) : oldVal;
                    const displayNew = typeof newVal === 'object' ? JSON.stringify(newVal) : newVal;

                    changesBody.insertAdjacentHTML('beforeend', `
                        <tr>
                            <td class="font-weight-bold text-gray-700" width="30%">${key}</td>
                            <td class="bg-danger-light text-danger">${displayOld ?? '<span class="text-muted">empty (null)</span>'}</td>
                            <td class="bg-success-light text-success">${displayNew ?? '<span class="text-muted">empty (null)</span>'}</td>
                        </tr>
                    `);
                }

                if (hasChanges) {
                    changesSection.style.display = 'block';
                } else {
                    changesSection.style.display = 'none';
                }

                const modalElement = document.getElementById('detailModal');
                $(modalElement).modal('show'); 
            }
        });
    });
</script>
@endpush