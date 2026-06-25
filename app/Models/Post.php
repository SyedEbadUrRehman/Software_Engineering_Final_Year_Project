<?php
namespace App\Models;

use App\Models\Circle;
use App\Models\Comment;
use App\Models\FollowerPostShare;
use App\Models\Like;
use App\Models\PostCircleShare;
use App\Models\PostReminder;
use App\Models\SavedPost;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use SoftDeletes;
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
    public function followerShares()
    {
        return $this->hasMany(FollowerPostShare::class, 'post_id');
    }

//  NEW: Relationship specifically for the auth user
    public function authUserFollowerShare()
    {
        return $this->hasOne(FollowerPostShare::class, 'post_id')
            ->where('shared_by_id', auth()->id());
    }


}
