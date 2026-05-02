<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class WorkflowStep extends Model
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'workflow_id',
        'tier',
        'division_id',
        'sla_days',
        'min_role_level'
    ];

    public function workflow()
    {
        return $this->belongsTo(Workflow::class, 'workflow_id');
    }

    public function division()
    {
        return $this->belongsTo(Division::class, 'division_id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'min_role_level', 'role_level');
    }
}
