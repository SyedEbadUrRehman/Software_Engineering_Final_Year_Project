<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostCircleShare extends Model
{
    use HasFactory;
     protected $fillable = [
        'post_id',
        'circle_id',
        'shared_by'
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function circle()
    {
        return $this->belongsTo(Circle::class);
    }

    public function sharer()
    {
        return $this->belongsTo(User::class, 'shared_by');
    }
}
