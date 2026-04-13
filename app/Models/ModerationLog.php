<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModerationLog extends Model
{
    use HasFactory;

    protected $fillable = [
    'moderatable_type',
    'moderatable_id',
    'action_taken',
    'api_response',
];
}
