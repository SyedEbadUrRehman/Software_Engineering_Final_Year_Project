<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FollowerPostShare extends Model
{
    use HasFactory;
    protected $table = 'follower_post_shares';

    protected $fillable = [
        'user_id',
        'post_id',
        'shared_by_id',
    ];
}
