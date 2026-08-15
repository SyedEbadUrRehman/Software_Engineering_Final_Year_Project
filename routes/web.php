<?php

use App\Events\TestEvent;
use App\Http\Controllers\Admin\ModerationController;
use App\Http\Controllers\AiIdeaController;
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
 Route::get('/', function () {
        return inertia::render('Index');
    });
Route::get('/privacy-policy', function () {
    return Inertia::render('PrivacyPolicy');
})->name('privacy.policy');
Route::get('/download-extension', function () {
    return Inertia::render('DownloadExtension');
})->name('extension.download');


// Main Group: Protected by 'auth' and '2fa' middlewares

Route::middleware(['auth', 'verified','2fa'])->group(function () {

    // ------------------------------------------------------------------
    // General Routes
    // ------------------------------------------------------------------
    Route::get('/a', fn() => view('welcome'));
    Route::get('/index', [HomeController::class, 'index'])->name('home.index');
    Route::get('/search', [SearchController::class, 'index'])->name('search.index');
    Route::get('/follow', [FollowController::class, 'index'])->name('follow.index');

    // ------------------------------------------------------------------
    // Users & Profiles (`/users`)
    // ------------------------------------------------------------------
    Route::prefix('users')->name('users.')->group(function () {
        Route::controller(UserController::class)->group(function () {
            Route::get('/{id}', 'show')->name('show');
            Route::post('/', 'update')->name('update');
            Route::put('/name', 'updateName')->name('name.update');
            Route::put('/bio', 'updateBio')->name('bio.update');
            Route::post('/two-factor', 'toggleTwoFactor')->name('two-factor.toggle');
            Route::delete('/account', 'destroyAccount')->name('account.destroy');
        });

        // Follow/Unfollow actions for user targets
        Route::post('/{user}/follow', [FollowController::class, 'follow'])->name('follow');
        Route::delete('/{user}/unfollow', [FollowController::class, 'unfollow'])->name('unfollow');
    });

    // ------------------------------------------------------------------
    // Active Sessions (`/sessions`)
    // ------------------------------------------------------------------
    Route::prefix('sessions')->name('sessions.')->controller(SessionController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::delete('/other', 'destroyOthers')->name('destroyOthers');
    });

    // ------------------------------------------------------------------
    // Posts (`/posts`)
    // ------------------------------------------------------------------
    Route::prefix('posts')->name('posts.')->group(function () {
        Route::controller(PostController::class)->group(function () {
            Route::post('/', 'store')->name('store');
            Route::delete('/{id}', 'destroy')->name('destroy');
            Route::put('/{post}/url', 'updateUrl')->name('updateUrl');
        });

        Route::post('/{post}/feedback', [PostFeedbackController::class, 'store'])->name('feedback.store');
        Route::post('/{post}/share', [PostCircleShareController::class, 'store'])->name('share');
        Route::post('/{post}/share-followers', [FollowController::class, 'shareToFollowers'])->name('share-followers');
        Route::delete('/{post}/unshare-followers', [FollowController::class, 'unshareFromFollowers'])->name('unshare-followers');
    });

    Route::delete('/post-circle-shares/{postCircleShare}', [PostCircleShareController::class, 'destroy'])
        ->name('posts.unshare');

    // ------------------------------------------------------------------
    // Comments (`/comments`)
    // ------------------------------------------------------------------
    Route::prefix('comments')->name('comments.')->controller(CommentController::class)->group(function () {
        Route::post('/', 'store')->name('store');
        Route::delete('/{id}', 'destroy')->name('destroy');
    });

    // ------------------------------------------------------------------
    // Likes (`/likes`)
    // ------------------------------------------------------------------
    Route::prefix('likes')->name('likes.')->controller(LikeController::class)->group(function () {
        Route::post('/', 'store')->name('store');
        Route::delete('/{id}', 'destroy')->name('destroy');
    });

    // ------------------------------------------------------------------
    // Circles & Circle Members
    // ------------------------------------------------------------------
    Route::get('/my-circles', [CircleController::class, 'myCircles'])->name('circles.my');

    Route::resource('circles', CircleController::class)
        ->only(['index', 'store', 'update', 'destroy']);

    Route::prefix('circles/{circle}/members')->name('circles.members.')->controller(CircleMemberController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::delete('/{user}', 'destroy')->name('destroy');
    });

    // ------------------------------------------------------------------
    // Saved Posts (`/saved` & `/saves`)
    // ------------------------------------------------------------------
    Route::controller(SavedPostController::class)->group(function () {
        Route::get('/saved', 'index')->name('saves.index');
        Route::prefix('saves')->name('saves.')->group(function () {
            Route::post('/', 'store')->name('store');
            Route::delete('/{savedPost}', 'destroy')->name('destroy');
        });
    });

    // ------------------------------------------------------------------
    // Notifications (`/notifications`)
    // ------------------------------------------------------------------
    Route::prefix('notifications')->name('notifications.')->controller(NotificationController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/read', 'markAsRead')->name('read');
    });

    // ------------------------------------------------------------------
    // Post Reminders (`/post-reminders`)
    // ------------------------------------------------------------------
    Route::prefix('post-reminders')->name('post-reminders.')->controller(PostReminderController::class)->group(function () {
        Route::post('/', 'store')->name('store');
        Route::put('/{postId}', 'update')->name('update');
        Route::delete('/{postId}', 'destroy')->name('destroy');
    });

    // AI Idea Generation Route
    Route::post('/posts/generate-ai-idea', [AiIdeaController::class, 'generate'])->name('posts.generate-ai');

});
/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Protected by 'auth' + the 'admin' middleware alias (see README) which
| only allows the ebaddev@gmail.com account through.
|
*/

Route::middleware(['auth', 'verified', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::prefix('moderation')->name('moderation.')->controller(ModerationController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::patch('/{type}/{id}', 'updateStatus')->name('update');
        });
    });

Route::get('/fire', function () {
    broadcast(new TestEvent());
    return 'Event fired';
});

require __DIR__ . '/auth.php';