<?php

namespace App\Providers;

use App\Models\DocumentApproval;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerBlueprintMacros();
        Paginator::useBootstrap();

        View::composer('layouts.sidebar', function ($view) {
            $user = auth()->user();
            $unopenedInboxCount = 0;

            if ($user && !$user->isSuperadmin()) {
                $organizationId = session('active_organization_id')
                    ?? $user->current_organization_id
                    ?? 1;
                $divisionId = session('active_division_id');

                $unopenedInboxCount = DocumentApproval::query()
                    ->where('document_approvals.approver_id', $user->id)
                    ->where('document_approvals.is_requester', false)
                    ->where('document_approvals.status', 'Pending')
                    ->where(function ($query) {
                        $query->where('document_approvals.flag_open', false)
                            ->orWhereNull('document_approvals.flag_open');
                    })
                    ->when($divisionId, fn ($query) => $query->where('document_approvals.division_id', $divisionId))
                    ->whereHas('document', function ($query) use ($organizationId) {
                        $query->where('organization_id', $organizationId)
                            ->where('status', '!=', 'Cancelled')
                            ->whereColumn('documents.current_tier', 'document_approvals.tier');
                    })
                    ->whereNotExists(function ($query) {
                        $query->selectRaw('1')
                            ->from('document_approvals as previous_approval')
                            ->whereColumn('previous_approval.document_id', 'document_approvals.document_id')
                            ->whereColumn('previous_approval.tier', 'document_approvals.tier')
                            ->whereColumn('previous_approval.approver_order', '<', 'document_approvals.approver_order')
                            ->where('previous_approval.status', 'Pending');
                    })
                    ->distinct('document_approvals.document_id')
                    ->count('document_approvals.document_id');
            }

            $view->with('unopenedInboxCount', $unopenedInboxCount);
        });
    }

    public function registerBlueprintMacros(): void
    {
        Blueprint::macro('baseColumns', function () {
            $this->foreignId('created_by')->nullable()->constrained('users');
            $this->foreignId('updated_by')->nullable()->constrained('users');
            $this->softDeletes();
        });
    }

}
