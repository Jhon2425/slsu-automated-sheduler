<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\FacultyController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Admin\ProgramController;
use App\Http\Controllers\Faculty\FacultyDashboardController;
use App\Http\Controllers\ProfileController;

// =================== Guest Routes ===================
Route::middleware('guest')->group(function () {
    Route::get('/', fn() => view('welcome'))->name('welcome');

    // Login
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    // Registration
    Route::get('/register/admin', [AuthController::class, 'showRegisterAdmin'])->name('register.admin');
    Route::post('/register/admin', [AuthController::class, 'registerAdmin']);

    Route::get('/register/faculty', [AuthController::class, 'showRegisterFaculty'])->name('register.faculty');
    Route::post('/register/faculty', [AuthController::class, 'registerFaculty']);

    Route::get('/register', fn() => view('welcome'))->name('register');
});

// =================== Authenticated Routes ===================
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Profile & settings
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/settings', fn() => view('settings'))->name('settings');

    // =================== Admin Routes ===================
    Route::prefix('admin')->middleware('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Faculty CRUD
        Route::resource('faculties', FacultyController::class);

        // Program management
        Route::get('programs', [ProgramController::class, 'index'])->name('programs.index');

        // Enrollment actions: Accept / Decline
        Route::post('/enrollments/{enrollment}/accept', [ProgramController::class, 'acceptEnrollment'])->name('enrollments.accept');
        Route::post('/enrollments/{enrollment}/decline', [ProgramController::class, 'declineEnrollment'])->name('enrollments.decline');

        // Enrollment management (edit/update)
        Route::get('/enrollments/{enrollment}/edit', [ProgramController::class, 'editEnrollment'])->name('enrollments.edit');
        Route::put('/enrollments/{enrollment}', [ProgramController::class, 'updateEnrollment'])->name('enrollments.update');

        // Assign schedules
        Route::get('/enrollments/{enrollment}/assign-schedule', [ProgramController::class, 'assignSchedule'])->name('enrollments.assign-schedule');
        Route::post('/enrollments/{enrollment}/store-schedule', [ProgramController::class, 'storeSchedule'])->name('enrollments.store-schedule');

        // Schedule management
        Route::get('/schedules', [ScheduleController::class, 'index'])->name('schedules.index');
        Route::post('/schedules/generate', [ScheduleController::class, 'generate'])->name('schedules.generate');
        Route::get('/schedules/download-pdf', [ScheduleController::class, 'downloadPDF'])->name('schedules.download');
    });

    // =================== Faculty Routes ===================
    Route::prefix('faculty')->middleware('faculty')->name('faculty.')->group(function () {
        Route::get('/dashboard', [FacultyDashboardController::class, 'index'])->name('dashboard');

        // Program enrollment/unenrollment
        Route::post('/programs/{program}/enroll', [FacultyDashboardController::class, 'enrollProgram'])->name('programs.enroll');
        Route::delete('/enrollments/{enrollment}/unenroll', [FacultyDashboardController::class, 'unenrollProgram'])->name('programs.unenroll');

        // Schedule viewing & download
        Route::get('/enrollments/{enrollment}/schedule', [FacultyDashboardController::class, 'viewSchedule'])->name('schedule.view');
        Route::get('/enrollments/{enrollment}/schedule/download', [FacultyDashboardController::class, 'downloadSchedule'])->name('schedule.download');

        // Legacy PDF download
        Route::get('/schedule/download-pdf', [FacultyDashboardController::class, 'downloadPDF'])->name('schedule.download-legacy');
    });
});
