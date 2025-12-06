<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\FacultyController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Admin\ProgramController;
use App\Http\Controllers\Faculty\FacultyDashboardController;
use App\Http\Controllers\ProfileController;

// Guest routes
Route::middleware('guest')->group(function () {
    // Welcome page
    Route::get('/', function () {
        return view('welcome');
    })->name('welcome');
    
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    
    // Separate registration routes for admin and faculty
    Route::get('/register/admin', [AuthController::class, 'showRegisterAdmin'])->name('register.admin');
    Route::post('/register/admin', [AuthController::class, 'registerAdmin']);
    
    Route::get('/register/faculty', [AuthController::class, 'showRegisterFaculty'])->name('register.faculty');
    Route::post('/register/faculty', [AuthController::class, 'registerFaculty']);
    
    // Generic register route redirects to role selection
    Route::get('/register', function() {
        return view('welcome');
    })->name('register');
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/settings', function() { return view('settings'); })->name('settings');

    // Admin routes
    Route::prefix('admin')->middleware('admin')->name('admin.')->group(function () {
        // Dashboard
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        
        // Faculty management
        Route::resource('faculties', FacultyController::class);
        
        // Schedule management
        Route::get('/schedules', [ScheduleController::class, 'index'])->name('schedules.index');
        Route::post('/schedules/generate', [ScheduleController::class, 'generate'])->name('schedules.generate');
        Route::get('/schedules/download-pdf', [ScheduleController::class, 'downloadPDF'])->name('schedules.download');

        // Program Management (Full CRUD)
        Route::resource('programs', ProgramController::class);
        
        // Enrollment Management
        Route::get('/enrollments/{enrollment}/edit', [ProgramController::class, 'editEnrollment'])->name('enrollments.edit');
        Route::put('/enrollments/{enrollment}', [ProgramController::class, 'updateEnrollment'])->name('enrollments.update');
        
        // Schedule Assignment for Enrollments
        Route::get('/enrollments/{enrollment}/assign-schedule', [ProgramController::class, 'assignSchedule'])->name('enrollments.assign-schedule');
        Route::post('/enrollments/{enrollment}/store-schedule', [ProgramController::class, 'storeSchedule'])->name('enrollments.store-schedule');
    });

    // Faculty routes
    Route::prefix('faculty')->middleware('faculty')->name('faculty.')->group(function () {
        // Dashboard (shows available and enrolled programs)
        Route::get('/dashboard', [FacultyDashboardController::class, 'index'])->name('dashboard');
        
        // Program Enrollment
        Route::post('/programs/{program}/enroll', [FacultyDashboardController::class, 'enrollProgram'])->name('programs.enroll');
        Route::delete('/enrollments/{enrollment}/unenroll', [FacultyDashboardController::class, 'unenrollProgram'])->name('programs.unenroll');
        
        // Schedule viewing and download
        Route::get('/enrollments/{enrollment}/schedule', [FacultyDashboardController::class, 'viewSchedule'])->name('schedule.view');
        Route::get('/enrollments/{enrollment}/schedule/download', [FacultyDashboardController::class, 'downloadSchedule'])->name('schedule.download');
        
        // Legacy route for backward compatibility
        Route::get('/schedule/download-pdf', [FacultyDashboardController::class, 'downloadPDF'])->name('schedule.download-legacy');
    });
});
