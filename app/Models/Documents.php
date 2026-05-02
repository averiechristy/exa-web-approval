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
        'current_tier'
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    public function folder()
    {
        return $this->belongsTo(Folder::class, 'folder_id');
    }

    public function documentapproval()
    {
        return $this->hasMany(DocumentApproval::class);
    }

    public function documentshare()
    {
        return $this->hasMany(DocumentShare::class);
    }
}
