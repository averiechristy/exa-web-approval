<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $fillable = [
        'system_role_id',
        'name',
        'email',
        'username',
        'is_active',
        'password'
    ];
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function userAccesses()
    {
        return $this->hasMany(UserAccess::class);
    }

    public function documentapproval()
    {
        return $this->hasMany(DocumentApproval::class);
    }

    public function systemRole()
    {
        return $this->belongsTo(SystemRole::class);
    }

    public function isSuperadmin()
    {
        return optional($this->systemRole)->id === 1;
    }
    
    public function access()
    {
        return $this->hasOne(UserAccess::class);
    }

}
