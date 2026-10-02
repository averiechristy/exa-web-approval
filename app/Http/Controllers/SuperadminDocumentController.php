<?php

namespace App\Http\Controllers;

use App\Models\Documents;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;

class SuperadminDocumentController extends Controller
{
    public function index(Request $request)
    {
        $documents = Documents::with(['requester', 'documentApprovals.approver', 'folder'])
            ->when($request->filled('organization_id'), function ($query) use ($request) {
                $query->where('organization_id', $request->input('organization_id'));
            })
            // Filter pencarian nama dokumen (CASE INSENSITIVE)
            ->when($request->filled('search'), function ($query) use ($request) {
                $searchTerm = '%' . strtolower($request->input('search')) . '%';
                $query->whereRaw('LOWER(document_name) LIKE ?', [$searchTerm]);
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->input('status'));
            })
            ->when($request->filled('requester_id'), function ($query) use ($request) {
                $query->where('requester_id', $request->input('requester_id'));
            })
            ->when($request->filled('from_date'), function ($query) use ($request) {
                $query->whereDate('created_at', '>=', $request->input('from_date'));
            })
            ->when($request->filled('to_date'), function ($query) use ($request) {
                $query->whereDate('created_at', '<=', $request->input('to_date'));
            })
            ->orderByDesc('updated_at')
            ->paginate($request->integer('perPage', 10))
            ->withQueryString();

        return view('superadmin.documents.index', [
            'documents' => $documents,
            'statuses'  => Documents::query()
                ->select('status')
                ->distinct()
                ->orderBy('status')
                ->pluck('status'),
            'requesters' => User::query()
                ->whereIn('id', Documents::query()->select('requester_id')->distinct())
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function preview(Documents $document)
    {
        $document->load(['requester', 'documentApprovals.approver', 'folder']);

        return view('superadmin.documents.preview', compact('document'));
    }

    public function void(Documents $document)
    {
        if ($document->status === 'Cancelled') {
            return back()->with('error', 'Dokumen sudah berstatus Void.');
        }

        $document->update(['status' => 'Cancelled']);

        return back()->with('success', 'Dokumen berhasil di-void.');
    }
}