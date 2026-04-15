<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Organization extends Model
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'organization_name'
    ];

    public function useraccess()
    {
        return $this->hasMany(UserAccess::class);
    }

    public function workflow()
    {
        return $this->hasMany(Workflow::class);
    }

    public function folder()
    {
        return $this->hasMany(Folder::class);
    }

    public function document()
    {
        return $this->hasMany(Documents::class);
    }

}
