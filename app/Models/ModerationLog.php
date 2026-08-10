<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModerationLog extends Model
{
    protected $fillable = [
        'moderatable_type',
        'moderatable_id',
        'action_taken',
        'api_response',
    ];

    protected $casts = [
        'api_response' => 'array',
    ];

    /**
     * The post or comment this log entry belongs to.
     * withTrashed() so deleted content still shows up in the admin table.
     */
    public function moderatable()
    {
        return $this->morphTo()->withTrashed();
    }
}
