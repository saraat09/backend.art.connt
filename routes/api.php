<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ArtisanController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\PaymentController;
 
/* ══════════════════════════════════════
 * Public routes
 * ══════════════════════════════════════ */
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login',    [AuthController::class, 'login']);
Route::get('/artisans',       [ArtisanController::class, 'index']);
Route::get('/artisans/{id}',  [ArtisanController::class, 'show']);

// Avis — lecture publique
Route::get('/artisans/{id}/reviews', [ReviewController::class, 'index']);

// Webhook Stripe (pas de JWT, signature Stripe à la place)
Route::post('/payments/webhook', [PaymentController::class, 'webhook']);

/* ══════════════════════════════════════
 * Protected routes (JWT)
 * ══════════════════════════════════════ */
Route::middleware('auth:api')->group(function () {

    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me',      [AuthController::class, 'me']);

    // Profile
    Route::put('/profile',        [ProfileController::class, 'update']);
    Route::post('/profile/photo', [ProfileController::class, 'uploadPhoto']);

    // Messages
    Route::get('/conversations',           [MessageController::class, 'conversations']);
    Route::get('/conversations/{userId}',  [MessageController::class, 'getMessages']);
    Route::post('/messages',               [MessageController::class, 'send']);
    Route::put('/messages/{id}/read',      [MessageController::class, 'markRead']);

    // Notifications
    Route::get('/notifications',           [NotificationController::class, 'index']);
    Route::put('/notifications/read-all',  [NotificationController::class, 'markAllRead']);
    Route::put('/notifications/{id}/read', [NotificationController::class, 'markRead']);
    Route::delete('/notifications/{id}',   [NotificationController::class, 'destroy']);

    // Avis — écriture protégée
    Route::post('/reviews',              [ReviewController::class, 'store']);
    Route::post('/reviews/{id}/reply',   [ReviewController::class, 'reply']);
    Route::get('/reviews/can-review/{artisanId}', [ReviewController::class, 'canReview']);

    // Disponibilité artisan
    Route::patch('/artisans/availability', [ArtisanController::class, 'toggleAvailability']);

    // Paiement Stripe
    Route::post('/payments/create-intent', [PaymentController::class, 'createIntent']);
});