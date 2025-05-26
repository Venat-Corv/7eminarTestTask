<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Middleware\ShareErrorsFromSession;

Route::middleware([
    EncryptCookies::class,
    AddQueuedCookiesToResponse::class,
    StartSession::class,
    ShareErrorsFromSession::class
])->post('/login', [AuthController::class, 'login']);

Route::get('/comments/search', [CommentController::class, 'search']);

Route::middleware(['custom.user.auth'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::resource('comments', CommentController::class)->except('search');
    Route::post('/post/{post}/comments', [CommentController::class, 'store']);
    Route::patch('/comments/{comment}/status', [CommentController::class, 'updateStatus']);
});

Route::get('/post/{post}/comments', [CommentController::class, 'index']);
