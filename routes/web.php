<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\FacultyController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Faculty\FacultyDashboardController;

// Guest routes
Route::middleware('guest')->group(function () {
    // Welcome page
    Route::get('/', function () {
        return view('welcome'); // ← show a welcome.blade.php page
    })->name('welcome');
    
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Profile routes
Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/settings', function() { return view('settings'); })->name('settings');

    // Admin routes
    Route::prefix('admin')->middleware('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        
        // Faculty management
        Route::resource('faculties', FacultyController::class);
        
        // Schedule management
        Route::get('/schedules', [ScheduleController::class, 'index'])->name('schedules.index');
        Route::post('/schedules/generate', [ScheduleController::class, 'generate'])->name('schedules.generate');
        Route::get('/schedules/download-pdf', [ScheduleController::class, 'downloadPDF'])->name('schedules.download');
    });
    
    // Faculty routes
    Route::prefix('faculty')->middleware('faculty')->name('faculty.')->group(function () {
        Route::get('/dashboard', [FacultyDashboardController::class, 'index'])->name('dashboard');
        Route::get('/schedule/download-pdf', [FacultyDashboardController::class, 'downloadPDF'])->name('schedule.download');
    });
});
