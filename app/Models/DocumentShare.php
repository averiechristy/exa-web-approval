<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class DocumentShare extends Model
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'document_id',
        'share_to',
        'share_by'
    ];

    public function document()
    {
        return $this->belongsTo(Documents::class, 'document_id');
    }
}
