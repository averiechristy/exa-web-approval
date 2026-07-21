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
    // 1. Ambil data sesi organisasi aktif & user login
    $activeOrgId = session('active_organization_id');
    $authUserId = auth()->id();

    // 2. Cek hak akses dan level role user login pada organisasi aktif
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

        $managerLevelThreshold = 3; // Sesuaikan threshold level manager-mu

        if ($roleName === 'MANAGER' || $roleLevel >= $managerLevelThreshold) {
            $showStaffFilter = true;

            // Ambil bawahan di divisi yang sama dengan level role di bawah manager
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

    // 3. Bangun Query Utama berbasis DocumentApproval
$query = DocumentApproval::query()
        ->with([
            'document.requester',
            'division',
            'approver'
        ])
        ->whereHas('document', function ($q) use ($activeOrgId) {
            $q->where('organization_id', $activeOrgId);
        });

    // ===== KUNCI FIX: SINKRONISASI DENGAN INBOX =====
    // Jangan hitung/tampilkan dokumen "Pending" jika belum giliran approver tersebut 
    // (Tier belum sampai / Order belum sampai)
    $query->where(function ($q) {
        // 1. Tetap hitung data historis yang sudah diselesaikan (Approved / Rejected)
        $q->whereIn('document_approvals.status', ['Approved', 'Rejected'])
          
          // 2. ATAU, hitung yang statusnya Pending, TAPI memang sudah giliran dia
          ->orWhere(function ($subQ) {
              $subQ->where('document_approvals.status', 'Pending')
                   // Syarat A: Tier-nya sama dengan current_tier di tabel documents
                   ->whereExists(function ($existDoc) {
                       $existDoc->select(DB::raw(1))
                           ->from('documents')
                           ->whereColumn('documents.id', 'document_approvals.document_id')
                           ->whereColumn('documents.current_tier', 'document_approvals.tier');
                   })
                   // Syarat B: Tidak ada approver lain sebelum dia (di tier yg sama) yang masih Pending
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

    // 4. LOGIKAL FILTER SCOPE TARGET APPROVER
    // Tentukan siapa target yang akan di-breakdown datanya
    $targetApproverId = ($showStaffFilter && $request->filled('staff_user_id')) 
        ? $request->staff_user_id 
        : $authUserId;

    if ($showStaffFilter && $request->filled('staff_user_id')) {
        // Jika manager memilih staff tertentu
        $query->where('approver_id', $targetApproverId);
    } else {
        // Default / Staff biasa: Hanya miliknya & abaikan dokumen buatannya sendiri jika ada
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
    }
        else {
        // Default jika user tidak memilih tanggal
        $query->whereDate('started_at', '>=', now()->subDays(30)); 
    }
    if ($request->filled('to_date')) {
        $query->whereDate('started_at', '<=', $request->to_date);
    }

    // Kunci base query yang sudah ter-filter untuk Summary & Widget
    $baseQuery = clone $query;

    // ==================== SUMMARY ====================
    $summary = [
        'total'    => $baseQuery->count(), // Menghitung total data approval yang ditargetkan
        'pending'  => (clone $baseQuery)->where('status', 'Pending')->count(),
        'approved' => (clone $baseQuery)->where('status', 'Approved')->count(),
        'rejected' => (clone $baseQuery)->where('status', 'Rejected')->count(),
        'overdue' => (clone $baseQuery)
                    ->where('status', 'Pending')
                    ->whereNull('completed_at')
                    ->where('due_at', '<', now())
                    ->count(),
    ];

    // Approved Hari Ini
    $summary['approved_today'] = (clone $baseQuery)
        ->where('status', 'Approved')
        ->whereDate('completed_at', \Carbon\Carbon::today()) // menggunakan kolom completed_at sesuai DBML jika selesai
        ->count();

    // Average Approval Time (Dalam Hari)
$avgTime = (clone $baseQuery)
    ->where('status', 'Approved')
    ->whereNotNull('started_at')
    ->whereNotNull('completed_at')
    ->selectRaw("AVG(EXTRACT(EPOCH FROM (completed_at - started_at)) / 86400) as avg_days")
    ->value('avg_days');

    $summary['avg_time'] = round($avgTime ?? 0, 1);

    // SLA Compliance
    $summary['compliance'] = $summary['approved'] == 0 
        ? 0 
        : round((($summary['approved'] - $summary['overdue']) / $summary['approved']) * 100, 2);

        // ==================== SLA COMPLIANCE EXTENDED ====================
    // 1. Hitung jumlah riil dokumen per kategori SLA
    // ==================== SLA COMPLIANCE EXTENDED (FIXED) ====================

// 1. Yang TEPAT WAKTU (Hanya yang sudah Approved dan selesai sebelum/pas due_at)
$slaSesuaiCount = (clone $baseQuery)
    ->where('status', 'Approved')
    ->where(function($q) {
        $q->whereNull('due_at')
          ->orWhereColumn('completed_at', '<=', 'due_at');
    })
    ->count();

// 2. Yang DI UJUNG TANDUK / BERESIKO (Masih Pending, belum overdue, tapi udah ngendap >= 3 hari)
$slaBeresikoCount = (clone $baseQuery)
    ->where('status', 'Pending')
    ->whereNull('completed_at')
    ->where('due_at', '>=', now())
    ->whereRaw('EXTRACT(DAY FROM (CURRENT_DATE - started_at)) >= 3')
    ->count();

// 3. Yang MELANGGAR SLA (Kasus si A masuk sini!)
$slaMelampauiCount = (clone $baseQuery)
    ->where(function ($q) {
        $q->where(function ($subQ) {
            // Kasus A: Sudah Approved/Rejected, tapi tanggal selesainya ngelewatin due_at
            $subQ->whereIn('status', ['Approved', 'Rejected'])
                 ->whereNotNull('due_at')
                 ->whereColumn('completed_at', '>', 'due_at');
        })->orWhere(function ($subQ) {
            // Kasus B: Masih Pending sampai sekarang, dan duedate-nya sudah lewat
            $subQ->where('status', 'Pending')
                 ->whereNull('completed_at')
                 ->where('due_at', '<', now());
        });
    })
    ->count();

// 2. Kalkulasi Persentase Baru
$totalSlaDocs = $slaSesuaiCount + $slaBeresikoCount + $slaMelampauiCount;

$compliancePct = $totalSlaDocs > 0 ? round(($slaSesuaiCount / $totalSlaDocs) * 100) : 0;
$beresikoPct   = $totalSlaDocs > 0 ? round(($slaBeresikoCount / $totalSlaDocs) * 100) : 0;
$melampauiPct  = $totalSlaDocs > 0 ? round(($slaMelampauiCount / $totalSlaDocs) * 100) : 0;

// Masukkan kembali ke summary untuk Blade
$summary['compliance']         = $compliancePct;
$summary['sla_beresiko_pct']   = $beresikoPct;
$summary['sla_melampaui_pct']  = $melampauiPct;

$summary['sla_sesuai_count']   = $slaSesuaiCount;
$summary['sla_beresiko_count'] = $slaBeresikoCount;
$summary['sla_melampaui_count'] = $slaMelampauiCount;
    // // ==================== STATUS BREAKDOWN ====================
    // $statusBreakdown = [
    //     'butuh_approval' => $summary['pending'],
    //     'approved'       => $summary['approved'],
    //     'rejected'       => $summary['rejected'],
    //     'overdue'        => $summary['overdue'],
    // ];

    // ==================== PENDING PRIORITIES ====================
    $pendingDocs = (clone $baseQuery)
        ->where('status', 'Pending')
        ->whereNull('completed_at') // Tambah ini jika ingin memastikan yg complete tidak masuk list pending
        ->orderByRaw("CASE 
            WHEN due_at < NOW() THEN 1 
            WHEN EXTRACT(DAY FROM (CURRENT_DATE - started_at)) >= 3 THEN 2 
            ELSE 3 END")
        ->limit(10)
        ->get()
        ->map(function($approval) {
            // Kalkulasi aging dinamis untuk tampilan list
            $started = \Carbon\Carbon::parse($approval->started_at);
            // Menggunakan round() untuk membulatkan ke angka terdekat
$approval->aging = $started->isToday() ? 0 : round($started->diffInDays(\Carbon\Carbon::now()));
            
            // Tentukan status overdue secara dinamis
            $isOverdue = $approval->due_at && \Carbon\Carbon::parse($approval->due_at)->isPast();
            
            if ($isOverdue) {
                $approval->priority = 'CRITICAL';
                $approval->priority_class = 'danger';
            } elseif ($approval->aging >= 3) {
                $approval->priority = 'HIGH';
                $approval->priority_class = 'warning';
            } else {
                $approval->priority = 'NORMAL';
                $approval->priority_class = 'primary';
            }
            return $approval;
        });

    // ==================== APPROVER WORKLOAD ====================
    $approverWorkload = (clone $baseQuery)
        ->where('status', 'Pending')
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

    // ==================== URGENT ALERTS ====================
   $urgentAlerts = (clone $baseQuery)
        ->where('status', 'Pending')
        ->whereNull('completed_at') // Supaya yang sudah complete tidak memicu alarm
        ->where(function($q) {
            $q->where('due_at', '<', now()) // Pengganti is_overdue = true
              ->orWhereRaw('EXTRACT(DAY FROM (CURRENT_DATE - started_at)) >= 5');
        })
        ->orderBy('started_at', 'asc') // Menampilkan yang paling lama mengendap duluan
        ->limit(5)
        ->get();

    // ==================== RECENT ACTIVITY ====================
    $recentActivities = DocumentApproval::with(['document.requester', 'approver'])
        ->whereHas('document', function ($q) use ($activeOrgId) {
            $q->where('organization_id', $activeOrgId);
        })
        ->latest('updated_at')
        ->limit(8)
        ->get();

    // ==================== GRAPH TREND 7 HARI ====================
    $trendLabels = [];
    $trendData = [];
    $slaData = [];

    for ($i = 6; $i >= 0; $i--) {
        $date = \Carbon\Carbon::today()->subDays($i);
        $trendLabels[] = $date->format('d M');
        
        // Filter clone khusus per tanggal perulangan
        $dayQuery = (clone $baseQuery)->whereDate('completed_at', $date);

        $approvedOnDay = (clone $dayQuery)->where('status', 'Approved')->count();
        $overdueOnDay  = (clone $dayQuery)->where('status', 'Approved')->where('is_overdue', true)->count();

        $trendData[] = $approvedOnDay;
        $slaData[]   = $approvedOnDay == 0 
            ? 0 
            : round((($approvedOnDay - $overdueOnDay) / $approvedOnDay) * 100, 2);
    }

    return view('dashboard.sla', [
        'summary'          => $summary,
        // 'statusBreakdown'  => $statusBreakdown,
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