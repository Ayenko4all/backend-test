<?php

use App\Http\Controllers\AchievementsController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\LessonController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::get('/', function () {
    return response()->json([
        'status' => 'success',
        'data'   => ['message' => 'Welcome to the User Achievement API'],
    ]);
})->name('index');

Route::post('comments/{user}', [CommentController::class, 'store'])->name('comment.store');
Route::get('/users/{user}/achievements', AchievementsController::class)->name('user.achievements');
Route::post('lessons/{lesson}/{user}',LessonController::class)->name('lesson.watched');
