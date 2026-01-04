<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\TestUploadController;

/*
|--------------------------------------------------------------------------
| Routes Web
|--------------------------------------------------------------------------
*/

// Page d'accueil → redirection vers le tableau de bord ou la page de connexion
Route::get('/', function () {
    return auth()->check() ? redirect('/dashboard') : redirect('/login');
})->name('home');

// Routes publiques (sans authentification)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Route de débogage
Route::get('/debug-history', [\App\Http\Controllers\AttendanceController::class, 'history']);

// Route de test d'authentification
Route::get('/test-auth', [\App\Http\Controllers\TestController::class, 'testAuth']);

// Route de test temporaire
Route::get('/test-history', function () {
    try {
        return 'Test route fonctionnelle - ' . (auth()->check() ? 'Connecté' : 'Non connecté');
    } catch (\Exception $e) {
        return 'Erreur: ' . $e->getMessage();
    }
})->name('test.history');

// Routes protégées (avec authentification)
Route::middleware('auth')->group(function () {
    // Déconnexion
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Tableau de bord
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    
    // Alias for admin dashboard
    Route::get('/admin/dashboard', function () {
        return redirect()->route('dashboard');
    });
    
    // Gestion des pointages - Nouvelles routes
    Route::prefix('attendance')->name('attendance.')->group(function () {
        Route::get('/', [AttendanceController::class, 'index'])->name('index');
        Route::post('/check-in', [AttendanceController::class, 'checkIn'])->name('check-in');
        Route::post('/check-out', [AttendanceController::class, 'checkOut'])->name('check-out');
        Route::get('/history', [AttendanceController::class, 'history'])->name('history');
    });
    
        // Gestion des permissions (utilisateur)
    Route::prefix('permissions')->name('permissions.')->group(function () {
        Route::get('/', [\App\Http\Controllers\PermissionController::class, 'index'])->name('index');
        Route::post('/store', [\App\Http\Controllers\PermissionController::class, 'store'])->name('store');
        Route::get('/my-requests', [\App\Http\Controllers\PermissionController::class, 'myRequests'])->name('my-requests');
    });
    
    // Administration des permissions
    Route::prefix('admin/permissions')->name('admin.permissions.')->middleware(['auth', 'admin'])->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\PermissionController::class, 'index'])->name('index');
        Route::get('/history', [\App\Http\Controllers\Admin\PermissionController::class, 'history'])->name('history');
        Route::get('/{permission}', [\App\Http\Controllers\Admin\PermissionController::class, 'show'])->name('show');
        Route::post('/{permission}/respond', [\App\Http\Controllers\Admin\PermissionController::class, 'respond'])->name('respond');
    });
    
    // Alias pour la compatibilité avec les anciennes routes (redirections)
    Route::post('/employee/check-in', [AttendanceController::class, 'checkIn'])->name('employee.check-in');
    Route::post('/employee/check-out', [AttendanceController::class, 'checkOut'])->name('employee.check-out');
    Route::get('/employee/history', [AttendanceController::class, 'history'])->name('employee.history');
    
    // Redirection de l'ancienne route vers la nouvelle
    Route::get('/employee/dashboard', function () {
        return redirect()->route('dashboard');
    });
    
    // Gestion des utilisateurs
    Route::prefix('admin')->group(function () {
        Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [App\Http\Controllers\UserController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\UserController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\UserController::class, 'store'])->name('store');
        Route::get('/{user}', [App\Http\Controllers\UserController::class, 'show'])->name('show');
        Route::get('/{user}/edit', [App\Http\Controllers\UserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [App\Http\Controllers\UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [App\Http\Controllers\UserController::class, 'destroy'])->name('destroy');
        
        // Routes supplémentaires pour les actions spécifiques
        Route::put('/{user}/change-password', [App\Http\Controllers\UserController::class, 'changePassword'])
            ->name('change-password');
        Route::put('/{user}/update-status', [App\Http\Controllers\UserController::class, 'updateStatus'])
            ->name('update-status');
        Route::put('/{user}/update-schedule', [App\Http\Controllers\UserController::class, 'updateSchedule'])
            ->name('update-schedule');
        Route::post('/{user}/resend-verification', [App\Http\Controllers\UserController::class, 'resendVerificationEmail'])
            ->name('resend-verification');
        }); // Fin du groupe users
    }); // Fin du groupe admin

    // Routes de test d'upload
    Route::get('/test-upload', [TestUploadController::class, 'showUploadForm'])->name('test.upload.form');
    Route::post('/test-upload', [TestUploadController::class, 'upload'])->name('test.upload');
});
Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
