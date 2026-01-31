<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Circle extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name'
    ];
     // Circle owner
    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Circle members
    public function members()
    {
        return $this->belongsToMany(
            User::class,
            'circle_members',
            'circle_id',
            'member_id'
        )->withTimestamps();
    }

    // Posts shared in this circle
   public function sharedPosts()
{
    return $this->belongsToMany(
        Post::class,
        'post_circle_shares'
    )
    ->withPivot('id')
    ->withTimestamps();
}

}
