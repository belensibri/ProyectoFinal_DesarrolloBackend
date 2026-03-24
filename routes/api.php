<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\FaqArticleController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Tickets
    Route::apiResource('tickets', TicketController::class)->except(['destroy']);
    Route::patch('tickets/{ticket}/assign', [TicketController::class, 'assign']);
    Route::patch('tickets/{ticket}/status', [TicketController::class, 'updateStatus']);

    // Comments & Attachments
    Route::post('tickets/{ticket}/comments', [CommentController::class, 'store']);
    Route::post('tickets/{ticket}/attachments', [AttachmentController::class, 'store']);

    // FAQ
    Route::apiResource('faq', FaqArticleController::class)->only(['index', 'store']);
});
