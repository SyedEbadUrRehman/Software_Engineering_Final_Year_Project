<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CircleMember extends Model
{
    use HasFactory;
    protected $fillable = [
        'circle_id',
        'member_id',
        'added_by'
    ];

    public function circle()
    {
        return $this->belongsTo(Circle::class);
    }

    public function member()
    {
        return $this->belongsTo(User::class, 'member_id');
    }
}
