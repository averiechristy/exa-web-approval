<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Folder extends Model
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'parent_id',
        'folder_name',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    public function document()
    {
        return $this->hasMany(Documents::class);
    }

    public function parent()
    {
        return $this->belongsTo(Folder::class, 'parent_id');
    }

   public function children()
    {
        return $this->hasMany(Folder::class, 'parent_id')
                    ->with('children'); // recursive
    }
}
