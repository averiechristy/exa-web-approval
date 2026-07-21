@extends('layouts.app')

@section('title', 'E-Document Approval Monitoring Dashboard')

@section('content')
<div class="container-fluid">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">E-Document Approval Monitoring Dashboard</h1>
        <div class="text-muted small">
            Last update: {{ now()->translatedFormat('d F Y H:i') }} WIB
        </div>
    </div>

    <!-- Filter Form -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('dashboard.sla') }}" class="row gx-3 gy-2 align-items-center">
                
                <!-- Filter Status -->
                <div class="col-sm-3">
                    <select name="status" class="form-control form-control-sm">
                        <option value="">-- All Statuses --</option>
                        <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                        <option value="Approved" {{ request('status') == 'Approved' ? 'selected' : '' }}>Approved</option>
                        <option value="Rejected" {{ request('status') == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>

                <!-- Filter Staff (Hanya muncul untuk Manager ke atas) -->
                @if($showStaffFilter)
                <div class="col-sm-3">
                    <select name="staff_user_id" class="form-control form-control-sm">
                        <option value="">-- My Own Performance --</option>
                        @foreach($staffUsers as $staff)
                            {{-- Mengambil info role staff yang aktif di organisasi saat ini --}}
                            @php 
                                $staffAccess = $staff->userAccesses->where('organization_id', session('active_organization_id'))->first();
                                $staffRoleName = $staffAccess && $staffAccess->role ? $staffAccess->role->role_name : 'Staff';
                            @endphp
                            <option value="{{ $staff->id }}" {{ request('staff_user_id') == $staff->id ? 'selected' : '' }}>
                                {{ $staff->name }} ({{ $staffRoleName }})
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <!-- Filter Range Date -->
                <div class="{{ $showStaffFilter ? 'col-sm-4' : 'col-sm-6' }} d-flex align-items-center">
                    <input type="date" name="from_date" class="form-control form-control-sm" value="{{ request('from_date') }}">
                    <span class="mx-2 text-muted">to</span>
                    <input type="date" name="to_date" class="form-control form-control-sm" value="{{ request('to_date') }}">
                </div>

                <!-- Tombol Submit -->
                <div class="col-sm-2 ps-sm-3">
                    <button type="submit" class="btn btn-primary btn-sm w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="row">
        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Documents</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $summary['total'] }}</div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending Approval</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $summary['pending'] }}</div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Approved Today</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $summary['approved_today'] }}</div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Rejected</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $summary['rejected'] }}</div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Overdue</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $summary['overdue'] }}</div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Avg Approval Time</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $summary['avg_time'] }} days</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row">
        <!-- Document Status (Doughnut Chart) -->
        <div class="col-xl-4 col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Document Status</h6>
                </div>
                <div class="card-body">
                    <canvas id="statusPieChart" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Trend Chart -->
        <div class="col-xl-4 col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Approval Trend</h6>
                </div>
                <div class="card-body">
                    <canvas id="trendChart" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- SLA Compliance Bar Chart -->
        <div class="col-xl-4 col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 d-flex justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">SLA Compliance</h6>
                    <span class="badge badge-success align-self-center">{{ $summary['compliance'] }}%</span>
                </div>
                <div class="card-body">
                    <canvas id="slaBarChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Approval per Approver Workload Chart -->
        <div class="col-xl-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Approver Pending Workload (Top 8)</h6>
                </div>
                <div class="card-body">
                    <canvas id="approverWorkloadChart" height="220"></canvas>
                </div>
            </div>
        </div>

        <!-- Pending Approval Priorities Table -->
        <div class="col-xl-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Pending Approval Priorities</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover text-nowrap">
                            <thead>
                                <tr>
                                    <th>Document Name</th>
                                    <th>Requester</th>
                                    <th>Current Approver</th>
                                    <th>Priority</th>
                                    <th>Aging</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pendingDocs as $doc)
                                <tr>
                                    <td>{{ $doc->document->document_name ?? 'N/A' }}</td>
                                    <td>{{ $doc->document->requester->name ?? 'N/A' }}</td>
                                    <td>{{ $doc->approver->name ?? 'N/A' }}</td>
                                    <td><span class="badge badge-{{ $doc->priority_class }}">{{ $doc->priority }}</span></td>
                                    <td class="text-danger font-weight-bold">{{ $doc->aging }} days</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">No pending documents found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Urgent / Overdue Alert -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-danger text-white">
            <h6 class="m-0 font-weight-bold">🚨 Urgent / Overdue Document Alert</h6>
        </div>
        <div class="card-body">
            <div class="row">
                @forelse($urgentAlerts as $alert)
                    <div class="col-md-4 mb-2">
                        <div class="border-left-danger p-3 bg-light rounded">
                            <strong class="text-danger">{{ $alert->document->document_name ?? 'N/A' }}</strong>
                            <div class="small text-muted">Approver: {{ $alert->approver->name ?? 'Unknown' }}</div>
                            <div class="small font-weight-bold text-dark">Pending since: {{ \Carbon\Carbon::parse($alert->started_at)->format('d M Y') }}</div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center text-muted">All clear! No overdue document alerts at the moment.</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Recent Activity / Audit Trail -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Recent Activity / Audit Trail</h6>
        </div>
        <div class="card-body">
            <ul class="list-group list-group-flush small">
                @forelse($recentActivities as $activity)
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <div>
                            <strong>{{ $activity->document->document_name ?? 'N/A' }}</strong> 
                            status changed to <span class="badge badge-secondary">{{ $activity->status }}</span> by {{ $activity->approver->name ?? 'System' }}
                        </div>
                        <span class="text-muted text-xs">{{ $activity->updated_at->diffForHumans() }}</span>
                    </li>
                @empty
                    <li class="list-group-item text-center text-muted">No recent activity available.</li>
                @endforelse
            </ul>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// 1. Status Doughnut Chart
new Chart(document.getElementById('statusPieChart'), {
    type: 'doughnut',
    data: {
        labels: ['Approved', 'Pending', 'Rejected'],
        datasets: [{
            data: [
                {{ $summary['approved'] }},
                {{ $summary['pending'] }},
                {{ $summary['rejected'] }}
            ],
            backgroundColor: ['#1cc88a', '#f6c23e', '#e74a3b'],
            hoverBackgroundColor: ['#17a673', '#dddfeb', '#be2617'],
            hoverBorderColor: "rgba(234, 236, 244, 1)",
        }]
    },
    options: {
        maintainAspectRatio: false,
    }
});

// 2. Trend Approval Line Chart
new Chart(document.getElementById('trendChart'), {
    type: 'line',
    data: {
        labels: @json($trendLabels),
        datasets: [{
            label: 'Approved Documents',
            data: @json($trendData),
            borderColor: '#4e73df',
            backgroundColor: 'rgba(78, 115, 223, 0.05)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        maintainAspectRatio: false,
    }
});

// 3. SLA Compliance Bar Chart
new Chart(document.getElementById('slaBarChart'), {
    type: 'bar',
    data: {
        labels: @json($slaLabels),
        datasets: [{
            label: 'SLA Rate (%)',
            data: @json($slaData),
            backgroundColor: '#36b9cc'
        }]
    },
    options: {
        maintainAspectRatio: false,
        scales: {
            y: {
                min: 0,
                max: 100
            }
        }
    }
});

// 4. Approver Workload Horizontal Bar Chart
new Chart(document.getElementById('approverWorkloadChart'), {
    type: 'bar',
    data: {
        labels: @json($approverLabels),
        datasets: [{
            label: 'Total Pending Docs',
            data: @json($approverData),
            backgroundColor: '#f6c23e'
        }]
    },
    options: {
        indexAxis: 'y',
        maintainAspectRatio: false
    }
});
</script>
@endpush