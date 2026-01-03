<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\AdminController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
*/

// Routes d'authentification
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    
    // Routes protégées
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);
    });
});

// Routes protégées
Route::middleware('auth:sanctum')->group(function () {
    // --- Pointage (Employé) ---
    Route::prefix('attendance')->group(function () {
        Route::post('/check-in', [AttendanceController::class, 'checkIn']);
        Route::post('/check-out', [AttendanceController::class, 'checkOut']);
        Route::get('/today', [AttendanceController::class, 'today']);
        Route::get('/history', [AttendanceController::class, 'history']);
    });

    // --- Permissions (Employé) ---
    Route::prefix('permissions')->group(function () {
        Route::post('/', [PermissionController::class, 'store']);
        Route::get('/my-requests', [PermissionController::class, 'myRequests']);
    });

    // --- Administration (Admin uniquement) ---
    Route::prefix('admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard']);
        Route::get('/attendances', [AdminController::class, 'attendances']);
        Route::get('/reports/employee/{userId}', [AdminController::class, 'employeeReport']);
        
        // Gestion des permissions
        Route::get('/permissions', [PermissionController::class, 'index']);
        Route::post('/permissions/{id}/approve', [PermissionController::class, 'approve']);
        Route::post('/permissions/{id}/reject', [PermissionController::class, 'reject']);
    });
});