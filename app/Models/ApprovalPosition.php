<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class ApprovalPosition extends Model
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'document_approval_id',
        'page_number',
        'pos_x_percent',
        'pos_y_percent'
    ];

    public function documentapproval()
    {
        return $this->belongsTo(DocumentApproval::class, 'document_approval_id');
    }

}
