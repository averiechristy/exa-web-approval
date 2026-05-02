<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Role extends Model
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'role_level',
        'role_name'
    ];

    public function useraccess()
    {
        return $this->hasMany(UserAccess::class);
    }

}
