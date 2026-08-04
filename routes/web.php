<?php

use App\Events\TestEvent;
use App\Http\Controllers\CircleController;
use App\Http\Controllers\CircleMemberController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PostCircleShareController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PostFeedbackController;
use App\Http\Controllers\PostReminderController;
use App\Http\Controllers\SavedPostController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
 Route::get('/home', function () {
        return inertia::render('Index');
    });
Route::middleware('auth')->group(function () {
    Route::get('/a', function () {
        return view('welcome');
    });
    Route::get('/', [HomeController::class, 'index'])->name('home.index');

    Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');
    Route::post('/users', [UserController::class, 'update'])->name('users.update');

    // Profile page additions
    Route::put('/users/name', [UserController::class, 'updateName'])->name('users.name.update');
    Route::put('/users/bio', [UserController::class, 'updateBio'])->name('users.bio.update');
    Route::post('/users/two-factor', [UserController::class, 'toggleTwoFactor'])->name('users.two-factor.toggle');
    Route::delete('/users/account', [UserController::class, 'destroyAccount'])->name('users.account.destroy');

    // Active sessions (profile page "Active Sessions" section)
    Route::get('/sessions', [SessionController::class, 'index'])->name('sessions.index');
    Route::delete('/sessions/other', [SessionController::class, 'destroyOthers'])->name('sessions.destroyOthers');

    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    Route::delete('/posts/{id}', [PostController::class, 'destroy'])->name('posts.destroy');
    // Post feedback (1/4/6/8/10 rating, feeds into the post owner's reach score)
    Route::post('/posts/{post}/feedback', [PostFeedbackController::class, 'store'])->name('posts.feedback.store');

    Route::post('/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::delete('/comments/{id}', [CommentController::class, 'destroy'])->name('comments.destroy');

    Route::post('/likes', [LikeController::class, 'store'])->name('likes.store');
    Route::delete('/likes/{id}', [LikeController::class, 'destroy'])->name('likes.destroy');

    // Circles CRUD
    Route::resource('circles', CircleController::class)
        ->only(['index', 'store', 'update', 'destroy']);
    Route::get('/my-circles', [CircleController::class, 'myCircles'])
        ->name('circles.my');

    // Circle Members (New Backend Direct System)
    Route::get('/circles/{circle}/members', [CircleMemberController::class, 'index'])
        ->name('circles.members.index');

    Route::post('/circles/{circle}/members', [CircleMemberController::class, 'store'])
        ->name('circles.members.store');

    Route::delete('/circles/{circle}/members/{user}', [CircleMemberController::class, 'destroy'])
        ->name('circles.members.destroy');

    // Share Posts into Circles
    Route::post('/posts/{post}/share', [PostCircleShareController::class, 'store'])
        ->name('posts.share');
    Route::delete('/post-circle-shares/{postCircleShare}', [PostCircleShareController::class, 'destroy']
    )->name('posts.unshare');

    // Save Feature
    Route::get('/saved', [SavedPostController::class, 'index'])
        ->name('saves.index');
    Route::post('/saves', [SavedPostController::class, 'store'])
        ->name('saves.store');

    Route::delete('/saves/{savedPost}', [SavedPostController::class, 'destroy'])
        ->name('saves.destroy');

    // search page route
    Route::get('/search', [SearchController::class, 'index'])->name('search.index');

    // follow feature
    Route::get('/follow', [FollowController::class, 'index'])->name('follow.index');
    Route::post('/users/{user}/follow', [FollowController::class, 'follow'])->name('users.follow');
    Route::delete('/users/{user}/unfollow', [FollowController::class, 'unfollow'])->name('users.unfollow');
    Route::post('/posts/{post}/share-followers', [FollowController::class, 'shareToFollowers'])->name('posts.share-followers');
    Route::delete('/posts/{post}/unshare-followers', [FollowController::class, 'unshareFromFollowers'])->name('posts.unshare-followers');

});

Route::middleware('auth')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
});

Route::middleware('auth')->group(function () {
    Route::post('/post-reminders', [PostReminderController::class, 'store'])->name('post-reminders.store');
    Route::put('/post-reminders/{postId}', [PostReminderController::class, 'update'])->name('post-reminders.update');
    Route::delete('/post-reminders/{postId}', [PostReminderController::class, 'destroy'])->name('post-reminders.destroy');
});

Route::get('/fire', function () {
    broadcast(new TestEvent());
    return 'Event fired';
});

require __DIR__ . '/auth.php';