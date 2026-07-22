<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class AuditTrailController extends Controller
{
    public function index(Request $request)
    {
        $query = Activity::with('causer')->latest();

        if ($request->filled('log_name')) {
            $query->where('log_name', $request->log_name);
        }

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                ->orWhere('properties->attributes->document_name', 'like', "%{$search}%")
                ->orWhere('properties->attributes->organization_name', 'like', "%{$search}%");
            });
        }

        $activities = $query->paginate(15)->withQueryString();
        
        $logNames = Activity::distinct()->pluck('log_name');

        return view('superadmin.audit-trail.index', compact('activities', 'logNames'));
    }

    public function show($id)
    {
        $activity = Activity::with('causer')->findOrFail($id);
        
        $properties = $activity->properties ? $activity->properties->toArray() : [];
        
        $attributes = $properties['attributes'] ?? [];
        $changes = $properties['changes'] ?? [];

        return response(
            view('superadmin.audit-trail.partials.detail', compact('activity', 'attributes', 'changes'))->render()
        );
    }
}