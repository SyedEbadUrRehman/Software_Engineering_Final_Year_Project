<?php

use App\Models\CircleMember;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});


// 2. Private Circle Channel (For "Post Shared" updates to members)
Broadcast::channel('circle.{circleId}', function ($user, $circleId) {
    // Check if the authenticated user exists in the circle_members table for this circle
    return CircleMember::where('circle_id', $circleId)
        ->where('member_id', $user->id)
        ->exists();
});

Broadcast::channel('chat.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});