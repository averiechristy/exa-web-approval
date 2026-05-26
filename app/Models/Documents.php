<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Documents extends Model
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'folder_id',
        'document_name',
        'path',
        'status',
        'requester_id',
        'requester_division_id',
        'workflow_id',
        'current_tier',
        'email_subject',
        'email_message'
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    public function folder()
    {
        return $this->belongsTo(Folder::class, 'folder_id');
    }

    public function documentApprovals()
    {
        return $this->hasMany(DocumentApproval::class, 'document_id', 'id');
    }
    public function documentshare()
    {
        return $this->hasMany(DocumentShare::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function getCurrentApprovers()
    {
        $currentTier = $this->documentapprovals()
                            ->where('status', 'pending')
                            ->min('tier');

        return $this->documentapprovals()
                    ->where('tier', $currentTier)
                    ->orderBy('order')
                    ->get();
    }

    public function isNextApprover($user)
    {
        $currentApprovers = $this->getCurrentApprovers();
        if ($currentApprovers->isEmpty()) return false;

        $firstPending = $currentApprovers->first();
        return $firstPending->approver_id === $user->id;
    }
}
