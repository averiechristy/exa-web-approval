<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use App\Models\User;

class AuditTrailController extends Controller
{
    public function index(Request $request)
    {
        $query = Activity::with('causer')->latest();

        // 1. Filter Log Name / Module
        if ($request->filled('log_name')) {
            $query->where('log_name', $request->log_name);
        }

        // 2. Filter Event / Action
        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        // 3. Filter User / Causer ID (Diperbaiki: dipindah ke atas sebelum paginate)
        if ($request->filled('causer_id')) {
            $query->where('causer_id', $request->causer_id)
                  ->where('causer_type', User::class); // Opsional: memastikan causer bertipe Model User
        }

        // 4. Filter Date Range
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Eksekusi Paginasi
        $activities = $query->paginate(15)->withQueryString();

        // Ambil data pendukung opsi dropdown
        $logNames = Activity::distinct()->whereNotNull('log_name')->pluck('log_name');
        $events   = Activity::distinct()->whereNotNull('event')->pluck('event');
        $users    = User::orderBy('name', 'asc')->get();

        return view('superadmin.audit-trail.index', compact('activities', 'logNames', 'events', 'users'));
    }

    public function show($id)
    {
        $activity = Activity::with('causer')->findOrFail($id);

        $properties = $activity->properties ? $activity->properties->toArray() : [];

        $attributes = $properties['attributes'] ?? [];
        $changes    = $properties['changes'] ?? [];

        return response(
            view('superadmin.audit-trail.partials.detail', compact('activity', 'attributes', 'changes'))->render()
        );
    }
}