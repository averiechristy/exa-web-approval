<?php

namespace App\Http\Controllers;

use App\Models\Documents;
use App\Models\User;
use Illuminate\Http\Request;

class SuperadminDocumentController extends Controller
{
    public function index(Request $request)
    {
        $organizationId = session('active_organization_id')
            ?? $request->user()->current_organization_id
            ?? 1;

        $documents = Documents::with(['requester', 'documentApprovals.approver', 'folder'])
            ->where('organization_id', $organizationId)
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('document_name', 'like', '%' . $request->input('search') . '%');
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->input('status'));
            })
            ->orderByDesc('updated_at')
            ->paginate($request->integer('perPage', 10))
            ->withQueryString();

        return view('superadmin.documents.index', [
            'documents' => $documents,
            'statuses' => Documents::query()
                ->where('organization_id', $organizationId)
                ->select('status')
                ->distinct()
                ->orderBy('status')
                ->pluck('status'),
        ]);
    }

    public function preview(Documents $document)
    {
        $this->ensureActiveOrganization($document);

        $document->load(['requester', 'documentApprovals.approver', 'folder']);

        return view('superadmin.documents.preview', compact('document'));
    }

    public function void(Documents $document)
    {
        $this->ensureActiveOrganization($document);

        if ($document->status === 'Cancelled') {
            return back()->with('error', 'Dokumen sudah berstatus Void.');
        }

        $document->update(['status' => 'Cancelled']);

        return back()->with('success', 'Dokumen berhasil di-void.');
    }

    private function ensureActiveOrganization(Documents $document): void
    {
        $organizationId = session('active_organization_id')
            ?? auth()->user()->current_organization_id
            ?? 1;

        abort_unless((int) $document->organization_id === (int) $organizationId, 404);
    }
}