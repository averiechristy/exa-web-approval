<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Activity extends Model
{
    // If your database table name is different (e.g., 'activity_logs'), change it here:
    protected $table = 'activity_logs'; 

    protected $guarded = [];

    /**
     * The attributes that should be cast.
     * This automatically turns your JSON column into a clean PHP array!
     */
    protected $casts = [
        'properties' => 'array',
    ];

    /**
     * Get the user who caused the activity.
     */
    public function causer(): BelongsTo
    {
        // Adjust the foreign key if it's named 'caused_by' or 'user_id' in your migration
        return $this->belongsTo(User::class, 'causer_id'); 
    }
}