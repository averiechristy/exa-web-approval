<?php

namespace App\Http\Controllers;

use App\Models\Documents;
use Illuminate\Http\Request;
use App\Models\DocumentApproval;
use App\Models\Organization;
use App\Models\Division;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SlaDashboardController extends Controller
{
    public function index(Request $request)
    {
        $activeOrgId = session('active_organization_id');
        $authUserId = auth()->id();

        // 1. Cek hak akses dan role level
        $currentAccess = \App\Models\UserAccess::with('role')
            ->where('user_id', $authUserId)
            ->where('organization_id', $activeOrgId)
            ->first();

        $showStaffFilter = false;
        $staffUsers = collect();

        if ($currentAccess && $currentAccess->role) {
            $roleName = strtoupper($currentAccess->role->role_name);
            $roleLevel = $currentAccess->role->role_level;
            $userDivisionId = $currentAccess->division_id;

            $managerLevelThreshold = 3;

            if ($roleName === 'MANAGER' || $roleLevel >= $managerLevelThreshold) {
                $showStaffFilter = true;

                $staffUsers = User::where('id', '!=', $authUserId)
                    ->whereHas('userAccesses', function($q) use ($activeOrgId, $userDivisionId, $roleLevel) {
                        $q->where('organization_id', $activeOrgId)
                        ->where('division_id', $userDivisionId)
                        ->whereHas('role', function($subQ) use ($roleLevel) {
                            $subQ->where('role_level', '<', $roleLevel);
                        });
                    })
                    ->orderBy('name')
                    ->get();
            }
        }

        // 2. Query Utama dengan Filter Eliminasi (Pruning)
        $query = DocumentApproval::query()
            ->with([
                'document.requester',
                'division',
                'approver'
            ])
            ->whereHas('document', function ($q) use ($activeOrgId) {
                $q->where('organization_id', $activeOrgId);
            });

        // Filter: Abaikan step approval yang muncul SETELAH dokumen di-reject di step sebelumnya
        $query->whereNotExists(function ($existReject) {
            $existReject->select(DB::raw(1))
                ->from('document_approvals as da_reject')
                ->whereColumn('da_reject.document_id', 'document_approvals.document_id')
                ->where('da_reject.status', 'Rejected')
                ->where(function ($cond) {
                    // Terjadi rejection di tier sebelumnya
                    $cond->whereColumn('da_reject.tier', '<', 'document_approvals.tier')
                        // Atau terjadi rejection di tier yang sama tapi oleh approver sebelum dia
                        ->orWhere(function ($sameTier) {
                            $sameTier->whereColumn('da_reject.tier', 'document_approvals.tier')
                                     ->whereColumn('da_reject.approver_order', '<', 'document_approvals.approver_order');
                        });
                });
        });

        // Filter Kelayakan Status Approval (Historis vs Pending Aktif)
        $query->where(function ($q) {
            $q->whereIn('document_approvals.status', ['Approved', 'Rejected'])
            ->orWhere(function ($subQ) {
                $subQ->where('document_approvals.status', 'Pending')
                    ->whereExists(function ($existDoc) {
                        $existDoc->select(DB::raw(1))
                            ->from('documents')
                            ->whereColumn('documents.id', 'document_approvals.document_id')
                            ->whereColumn('documents.current_tier', 'document_approvals.tier');
                    })
                    ->whereNotExists(function ($existDa) {
                        $existDa->select(DB::raw(1))
                            ->from('document_approvals as da2')
                            ->whereColumn('da2.document_id', 'document_approvals.document_id')
                            ->whereColumn('da2.tier', 'document_approvals.tier')
                            ->whereColumn('da2.approver_order', '<', 'document_approvals.approver_order')
                            ->where('da2.status', 'Pending');
                    });
            });
        });

        $targetApproverId = ($showStaffFilter && $request->filled('staff_user_id')) 
            ? $request->staff_user_id 
            : $authUserId;

        if ($showStaffFilter && $request->filled('staff_user_id')) {
            $query->where('approver_id', $targetApproverId);
        } else {
            $query->where('approver_id', $targetApproverId)
                ->whereHas('document', function ($q) use ($authUserId) {
                    $q->where('requester_id', '!=', $authUserId);
                });
        }

        // === FILTERS DARI INPUT USER ===
        if ($request->filled('division_id')) {
            $query->where('division_id', $request->division_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('from_date')) {
            $query->whereDate('started_at', '>=', $request->from_date);
        } else {
            $query->whereDate('started_at', '>=', now()->subDays(30)); 
        }
        if ($request->filled('to_date')) {
            $query->whereDate('started_at', '<=', $request->to_date);
        }

        $baseQuery = clone $query;

        // ==================== REVISI KALKULASI SUMMARY ====================

        // Total data approval yang relevan
        $totalCount = $baseQuery->count();

        // 1. Pending: Hanya yang memang masih pending dan Dokumennya belum Rejected
        $pendingCount = (clone $baseQuery)
            ->where('document_approvals.status', 'Pending')
            ->whereHas('document', fn($d) => $d->where('status', '!=', 'Rejected'))
            ->count();

        // 2. Approved: Hanya yang status approval-nya Approved DAN status Dokumen Utamanya JUGA Approved
        $approvedCount = (clone $baseQuery)
            ->where('document_approvals.status', 'Approved')
            ->whereHas('document', fn($d) => $d->where('status', 'Approved'))
            ->count();

        // 3. Rejected: Approval yang di-reject LANGSUNG oleh dirinya OR Approval milik dia yang tadinya Approved tapi DOKUMEN AKHIRNYA di-reject oleh approver tingkat lanjut
        $rejectedCount = (clone $baseQuery)
            ->where(function ($q) {
                $q->where('document_approvals.status', 'Rejected')
                  ->orWhere(function ($sub) {
                      $sub->where('document_approvals.status', 'Approved')
                          ->whereHas('document', fn($d) => $d->where('status', 'Rejected'));
                  });
            })
            ->count();

        // 4. Overdue
        $overdueCount = (clone $baseQuery)
            ->where('document_approvals.status', 'Pending')
            ->whereNull('completed_at')
            ->where('due_at', '<', now())
            ->whereHas('document', fn($d) => $d->where('status', '!=', 'Rejected'))
            ->count();

        // 5. Approved Hari Ini
        $approvedTodayCount = (clone $baseQuery)
            ->where('document_approvals.status', 'Approved')
            ->whereHas('document', fn($d) => $d->where('status', 'Approved'))
            ->whereDate('completed_at', Carbon::today())
            ->count();

        $summary = [
            'total'          => $totalCount,
            'pending'        => $pendingCount,
            'approved'       => $approvedCount,
            'rejected'       => $rejectedCount,
            'overdue'        => $overdueCount,
            'approved_today' => $approvedTodayCount,
        ];

        // Average Approval Time (Hanya dihitung dari dokumen yang benar-benar Approved hingga akhir)
        $avgTime = (clone $baseQuery)
            ->where('document_approvals.status', 'Approved')
            ->whereHas('document', fn($d) => $d->where('status', 'Approved'))
            ->whereNotNull('started_at')
            ->whereNotNull('completed_at')
            ->selectRaw("AVG(EXTRACT(EPOCH FROM (completed_at - started_at)) / 86400) as avg_days")
            ->value('avg_days');

        $summary['avg_time'] = round($avgTime ?? 0, 1);

        // SLA Analytics Calculation
        $slaSesuaiCount = (clone $baseQuery)
            ->where('document_approvals.status', 'Approved')
            ->whereHas('document', fn($d) => $d->where('status', 'Approved'))
            ->where(function($q) {
                $q->whereNull('due_at')
                ->orWhereColumn('completed_at', '<=', 'due_at');
            })
            ->count();

        $slaBeresikoCount = (clone $baseQuery)
            ->where('document_approvals.status', 'Pending')
            ->whereNull('completed_at')
            ->where('due_at', '>=', now())
            ->whereRaw('EXTRACT(DAY FROM (CURRENT_DATE - started_at)) >= 3')
            ->whereHas('document', fn($d) => $d->where('status', '!=', 'Rejected'))
            ->count();

        $slaMelampauiCount = (clone $baseQuery)
            ->where(function ($q) {
                $q->where(function ($subQ) {
                    $subQ->whereIn('document_approvals.status', ['Approved', 'Rejected'])
                        ->whereNotNull('due_at')
                        ->whereColumn('completed_at', '>', 'due_at');
                })->orWhere(function ($subQ) {
                    $subQ->where('document_approvals.status', 'Pending')
                        ->whereNull('completed_at')
                        ->where('due_at', '<', now());
                });
            })
            ->count();

        $totalSlaDocs = $slaSesuaiCount + $slaBeresikoCount + $slaMelampauiCount;

        $summary['compliance']         = $totalSlaDocs > 0 ? round(($slaSesuaiCount / $totalSlaDocs) * 100) : 0;
        $summary['sla_beresiko_pct']   = $totalSlaDocs > 0 ? round(($slaBeresikoCount / $totalSlaDocs) * 100) : 0;
        $summary['sla_melampaui_pct']  = $totalSlaDocs > 0 ? round(($slaMelampauiCount / $totalSlaDocs) * 100) : 0;

        $summary['sla_sesuai_count']   = $slaSesuaiCount;
        $summary['sla_beresiko_count'] = $slaBeresikoCount;
        $summary['sla_melampaui_count'] = $slaMelampauiCount;

        // Pending Priorities
        $pendingDocs = (clone $baseQuery)
            ->where('document_approvals.status', 'Pending')
            ->whereHas('document', fn($d) => $d->where('status', '!=', 'Rejected'))
            ->whereNull('completed_at')
            ->where('due_at', '>=', now())
            ->orderByRaw("CASE 
                WHEN due_at < NOW() THEN 1 
                WHEN EXTRACT(DAY FROM (CURRENT_DATE - started_at)) >= 3 THEN 2 
                ELSE 3 END")
            ->limit(10)
            ->get()
            ->map(function($approval) {
                $started = Carbon::parse($approval->started_at);
                $approval->aging = $started->isToday() ? 0 : round($started->diffInDays(Carbon::now()));

                if ($approval->due_at) {
                    $now = Carbon::now();
                    $dueAt = Carbon::parse($approval->due_at);
                    $hoursLeft = $now->diffInHours($dueAt, false);

                    if ($hoursLeft <= 12) {
                        $approval->priority = 'CRITICAL';
                        $approval->priority_class = 'danger';
                    } elseif ($hoursLeft <= 24) {
                        $approval->priority = 'HIGH';
                        $approval->priority_class = 'warning';
                    } else {
                        $approval->priority = 'NORMAL';
                        $approval->priority_class = 'primary';
                    }
                } else {
                    $approval->priority = 'NORMAL';
                    $approval->priority_class = 'primary';
                }

                return $approval;
            });

        // Approver Workload
        $approverWorkload = (clone $baseQuery)
            ->where('document_approvals.status', 'Pending')
            ->whereHas('document', fn($d) => $d->where('status', '!=', 'Rejected'))
            ->select('approver_id')
            ->selectRaw('COUNT(*) as total_pending')
            ->selectRaw('SUM(CASE WHEN is_overdue = true THEN 1 ELSE 0 END) as overdue_count')
            ->groupBy('approver_id')
            ->with('approver')
            ->orderByDesc('total_pending')
            ->limit(8)
            ->get();

        $approverLabels = $approverWorkload->map(fn($w) => $w->approver->name ?? 'Unknown')->toArray();
        $approverData = $approverWorkload->map(fn($w) => $w->total_pending)->toArray();

        // Urgent Alerts
        $urgentAlerts = (clone $baseQuery)
            ->where('document_approvals.status', 'Pending')
            ->whereHas('document', fn($d) => $d->where('status', '!=', 'Rejected'))
            ->whereNull('completed_at')
            ->where(function($q) {
                $q->where('due_at', '<', now())
                ->orWhereRaw('EXTRACT(DAY FROM (CURRENT_DATE - started_at)) >= 5');
            })
            ->orderBy('started_at', 'asc')
            ->get();

        // Recent Activities
        $recentActivities = DocumentApproval::with(['document.requester', 'approver'])
            ->whereHas('document', function ($q) use ($activeOrgId) {
                $q->where('organization_id', $activeOrgId);
            })
            ->latest('updated_at')
            ->limit(8)
            ->get();

        // Trend Graph
        $trendLabels = [];
        $trendData   = [];
        $slaData     = [];

        $startDate = $request->filled('from_date') 
            ? Carbon::parse($request->from_date)->startOfDay() 
            : Carbon::today()->subDays(29)->startOfDay();

        $endDate = $request->filled('to_date') 
            ? Carbon::parse($request->to_date)->endOfDay() 
            : Carbon::today()->endOfDay();

        if ($startDate->gt($endDate)) {
            $startDate = (clone $endDate)->subDays(29)->startOfDay();
        }

        $currentDate = clone $startDate;
        while ($currentDate->lte($endDate)) {
            $trendLabels[] = $currentDate->format('d M');
            
            $dayQuery = (clone $baseQuery)->whereDate('completed_at', $currentDate->format('Y-m-d'));

            $approvedOnDay = (clone $dayQuery)
                ->where('document_approvals.status', 'Approved')
                ->whereHas('document', fn($d) => $d->where('status', 'Approved'))
                ->count();
            
            $overdueOnDay  = (clone $dayQuery)
                ->where('document_approvals.status', 'Approved')
                ->whereHas('document', fn($d) => $d->where('status', 'Approved'))
                ->where(function($q) {
                    $q->whereNotNull('due_at')
                    ->whereColumn('completed_at', '>', 'due_at');
                })->count();

            $trendData[] = $approvedOnDay;
            $slaData[]   = $approvedOnDay == 0 
                ? 0 
                : round((($approvedOnDay - $overdueOnDay) / $approvedOnDay) * 100, 2);

            $currentDate->addDay();
        }

        return view('dashboard.sla', [
            'summary'          => $summary,
            'pendingDocs'      => $pendingDocs,
            'approverWorkload' => $approverWorkload,
            'urgentAlerts'     => $urgentAlerts,
            'recentActivities' => $recentActivities,
            'trendLabels'      => $trendLabels,
            'trendData'        => $trendData,
            'slaLabels'        => $trendLabels, 
            'slaData'          => $slaData,
            'approverLabels'   => $approverLabels,
            'approverData'     => $approverData,
            'showStaffFilter'  => $showStaffFilter,
            'staffUsers'       => $staffUsers,
            'organizations'    => Organization::orderBy('organization_name')->get(),
            'divisions'        => Division::orderBy('division_name')->get(),
            'approvers'        => User::orderBy('name')->get(),
        ]);
    }
}