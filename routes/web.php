<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CircleController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\SavedPostController;
use App\Http\Controllers\CircleMemberController;
use App\Http\Controllers\PostCircleShareController;

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

Route::middleware('auth')->group(function () {
    Route::get('/a', function () {
        return view('welcome');
    });
    Route::get('/', [HomeController::class, 'index'])->name('home.index');

    Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');
    Route::post('/users', [UserController::class, 'update'])->name('users.update');

    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    Route::delete('/posts/{id}', [PostController::class, 'destroy'])->name('posts.destroy');

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
    Route::get('/saved',[SavedPostController::class, 'index'])
        ->name('saves.index');
    Route::post('/saves', [SavedPostController::class, 'store'])
        ->name('saves.store');

    Route::delete('/saves/{savedPost}', [SavedPostController::class, 'destroy'])
        ->name('saves.destroy');

});

require __DIR__ . '/auth.php';
