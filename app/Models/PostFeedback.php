<?php

namespace App\Models;

use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostFeedback extends Model
{
    use HasFactory;
    protected $table = 'post_feedbacks';
    protected $fillable = [
        'user_id',
        'post_id',
        'post_owner_id',
        'rating',
    ];
 
    /**
     * Only these 5 values are ever valid, even though the column is a
     * plain tinyint. Enforced in the FormRequest/Controller layer, kept
     * here as the single source of truth so frontend + backend agree.
     */
    public const ALLOWED_RATINGS = [1, 4, 6, 8, 10];
 
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
 
    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }
 
    public function postOwner()
    {
        return $this->belongsTo(User::class, 'post_owner_id');
    }
}
