<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CarController;
use App\Http\Controllers\RentalController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;

// Public Routes (No login needed)

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public Car Routes
Route::get('/cars', [CarController::class, 'index']);
Route::get('/cars/{id}', [CarController::class, 'show']);
Route::get('/cars/{id}/availability', [CarController::class, 'checkAvailability']);

// Protected Routes (Must be logged in)
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Car Management (Agents & Admins)
    Route::middleware(['role:admin,agent'])->group(function () {
        Route::post('/cars', [CarController::class, 'store']);
        Route::put('/cars/{id}', [CarController::class, 'update']);
        Route::delete('/cars/{id}', [CarController::class, 'destroy']);

        // Agent/Admin: View All Rentals & Update Status
        Route::get('/rentals', [RentalController::class, 'index']);
        Route::put('/rentals/{id}/status', [RentalController::class, 'updateStatus']);
        Route::post('/rentals/on-site', [RentalController::class, 'storeOnSite']);
    });

    // User: Rental Requests
    Route::post('/rentals', [RentalController::class, 'store']);       // Make a request
    Route::post('/rentals/{id}/payment', [RentalController::class, 'uploadPayment']);
    Route::get('/rentals/{id}/payment-proof', [RentalController::class, 'getPaymentProof']);
    Route::get('/rentals/{id}/pdf', [RentalController::class, 'downloadPdf']);
    Route::get('/users/{id}/documents/{type}', [RentalController::class, 'getUserDocument']);
    Route::get('/my-rentals', [RentalController::class, 'myRentals']); // View history

    // Admin: User Management
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/admin/stats', [DashboardController::class, 'stats']);
        Route::get('/users', [UserController::class, 'index']);
        Route::delete('/users/{id}', [UserController::class, 'destroy']);
    });
    // Notifications
    Route::get('/notifications', [App\Http\Controllers\NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [App\Http\Controllers\NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [App\Http\Controllers\NotificationController::class, 'markAllAsRead']);
});
