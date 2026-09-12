<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Division extends Model
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'division_name',
        'organization_id'
    ];

    public function useraccess()
    {
        return $this->hasMany(UserAccess::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function workflowstep()
    {
        return $this->hasMany(WorkflowStep::class);
    }

    public function documentapproval()
    {
        return $this->hasMany(DocumentApproval::class);
    }
}
