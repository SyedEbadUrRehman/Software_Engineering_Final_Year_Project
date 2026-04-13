<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;
    protected $fillable = [
        'status',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class)->orderBy('created_at', 'desc');
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }
    public function sharedCircles()
    {
        return $this->belongsToMany(Circle::class, 'post_circle_shares')
            ->withPivot('id') // ✅ REQUIRED
            ->withTimestamps();
    }
    public function saves()
    {
        return $this->hasMany(SavedPost::class);
    }

    public function circleShares()
    {
        return $this->hasMany(PostCircleShare::class);
    }
    public function reminders()
    {
        return $this->hasMany(PostReminder::class);
    }
}
