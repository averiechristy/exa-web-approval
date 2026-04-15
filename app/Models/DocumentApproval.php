<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class DocumentApproval extends Model
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'document_id',
        'division_id',
        'approver_id',
        'approver_order',
        'show_on_doc',
        'status',
        'approved_at',
        'remarks',
    ];

    public function document()
    {
        return $this->belongsTo(Documents::class, 'document_id');
    }
    public function division()
    {
        return $this->belongsTo(Division::class, 'division_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function approvalposition()
    {
        return $this->hasMany(ApprovalPosition::class);
    }

}
