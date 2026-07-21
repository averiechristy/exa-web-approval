<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity; // Use Spatie's official model

class AuditTrailController extends Controller
{
    public function index(Request $request)
    {
        $query = Activity::with('causer')->latest();

        // Filter by Module / Log Name (e.g., 'document')
        if ($request->filled('log_name')) {
            $query->where('log_name', $request->log_name);
        }

        // Filter by Action / Event (e.g., 'document.rejected')
        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        // Text search inside description or JSON properties
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                ->orWhere('properties->attributes->document_name', 'like', "%{$search}%")
                ->orWhere('properties->attributes->organization_name', 'like', "%{$search}%");
            });
        }

        $activities = $query->paginate(15)->withQueryString();
        
        // Clean and standard pluck for the dropdown filters
        $logNames = Activity::distinct()->pluck('log_name');

        return view('superadmin.audit-trail.index', compact('activities', 'logNames'));
    }

public function show($id)
{
    $activity = Activity::with('causer')->findOrFail($id);
    
    // Ambil collection properties, ubah ke array
    $properties = $activity->properties ? $activity->properties->toArray() : [];
    
    // Pecah data berdasarkan struktur hasil dd() tadi
    $attributes = $properties['attributes'] ?? [];
    $changes = $properties['changes'] ?? [];

    return response(
        view('superadmin.audit-trail.partials.detail', compact('activity', 'attributes', 'changes'))->render()
    );
}
}