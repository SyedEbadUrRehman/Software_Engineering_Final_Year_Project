<?php
namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class AllPostsCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request)
    {
        // return $this->collection->map(function ($post) {
        //     return [
        //         'id' => $post->id,
        //         'text' => $post->text,
        //         'file' => $post->file,
        //         'created_at' => $post->created_at->format(' M D Y'),
        //         'comments' => $post->comments->map(function ($comment) {
        //             return [
        //                 'id' => $comment->id,
        //                 'text' => $comment->text,
        //                 'user' => [
        //                     'id' => $comment->user->id,
        //                     'name' => $comment->user->name,
        //                     'file' => $comment->user->file
        //                 ],
        //             ];
        //         }),
        //         'likes' => $post->likes->map(function ($like) {
        //             return [
        //                 'id' => $like->id,
        //                 'user_id' => $like->user_id,
        //                 'post_id' => $like->post_id
        //             ];
        //         }),
        //         'user' => [
        //             'id' => $post->user->id,
        //             'name' => $post->user->name,
        //             'file' => $post->user->file
        //         ]
        //     ];
        // });
        return $this->collection->map(function ($post) {

            return [
                'id'                   => $post->id,
                'text'                 => $post->text,
                'url'                  => $post->url,
                'file'                 => $post->file,
                'status'               => $post->status,
                'created_at'           => $post->created_at->format(' M D Y'),

                // ✅ Shared Circles Info
                'shared_circles_count' => $post->sharedCircles->count(),

                'shared_circles'       => $post->sharedCircles->map(function ($circle) {
                    return [
                        'id'       => $circle->id,
                        'name'     => $circle->name,

                        // pivot table id (post_circle_shares row id)
                        'share_id' => $circle->pivot->id,
                    ];
                }),
                'saves'                => $post->saves->map(function ($save) {
                    return [
                        'id'      => $save->id,
                        'user_id' => $save->user_id,
                    ];
                }),
                'reminders'            => $post->reminders->map(function ($reminder) {
                    return [
                        'id'      => $reminder->id,
                        'user_id' => $reminder->user_id,
                        // Format specifically for the <input type="date"> field
                        'due_at'  => Carbon::parse($reminder->due_at)->format('Y-m-d'),
                        'sent_at' => $reminder->sent_at,
                    ];
                }),

                // ✅ Comments
                'comments'             => $post->comments->map(function ($comment) {
                    return [
                        'id'   => $comment->id,
                        'text' => $comment->text,
                        'status'=>$comment->status,
                        'user' => [
                            'id'   => $comment->user->id,
                            'name' => $comment->user->name,
                            'file' => $comment->user->file,
                        ],
                    ];
                }),

                // ✅ Likes
                'likes'                => $post->likes->map(function ($like) {
                    return [
                        'id'      => $like->id,
                        'user_id' => $like->user_id,
                        'post_id' => $like->post_id,
                    ];
                }),

                // ✅ Post Owner
                'user'                 => [
                    'id'   => $post->user->id,
                    'name' => $post->user->name,
                    'file' => $post->user->file,
                ],
            ];
        });
    }
}
