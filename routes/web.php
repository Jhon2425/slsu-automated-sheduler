<?php

// Updated routes/web.php - Add these routes to your existing file

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Admin\ProgramController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\FacultyController;
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

        // Faculty Management
        Route::get('/faculty', [FacultyController::class, 'index'])->name('faculty.index');
        
        // Faculty Subject Assignment (AJAX endpoints)
        Route::get('/faculty/{user}/subjects', [FacultyController::class, 'getSubjects'])->name('faculty.subjects');
        Route::post('/faculty/{user}/assign-subjects', [FacultyController::class, 'assignSubjects'])->name('faculty.assign-subjects');

        // Subject Management (both route names for compatibility)
        Route::resource('subjects', SubjectController::class)->except(['show']);
        // Alias routes for backward compatibility
        Route::get('/manage-subjects', [SubjectController::class, 'index'])->name('manage-subjects.index');
        Route::post('/manage-subjects', [SubjectController::class, 'store'])->name('manage-subjects.store');
        Route::get('/manage-subjects/create', [SubjectController::class, 'create'])->name('manage-subjects.create');
        Route::put('/manage-subjects/{subject}', [SubjectController::class, 'update'])->name('manage-subjects.update');
        Route::delete('/manage-subjects/{subject}', [SubjectController::class, 'destroy'])->name('manage-subjects.destroy');
        Route::get('/manage-subjects/{subject}/edit', [SubjectController::class, 'edit'])->name('manage-subjects.edit');

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

        // =================== Enhanced Schedule Management ===================
        Route::prefix('schedules')->name('schedules.')->group(function () {
            // Existing routes
            Route::get('/', [ScheduleController::class, 'index'])->name('index');
            Route::post('/generate', [ScheduleController::class, 'generate'])->name('generate');
            Route::get('/download-pdf', [ScheduleController::class, 'downloadPDF'])->name('download');
            
            // New relationship-based routes
            Route::post('/save', [ScheduleController::class, 'save'])->name('save');
            Route::post('/check-conflicts', [ScheduleController::class, 'checkConflicts'])->name('checkConflicts');
            
            // View schedules by entity with relationships
            Route::get('/faculty/{facultyId}', [ScheduleController::class, 'getFacultySchedule'])->name('faculty');
            Route::get('/room/{roomId}', [ScheduleController::class, 'getRoomSchedule'])->name('room');
            Route::get('/program/{programId}', [ScheduleController::class, 'getProgramSchedule'])->name('program');
            
            // Get all programs for schedule generation UI
            Route::get('/programs-list', [ScheduleController::class, 'getPrograms'])->name('programs');
        });
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
        
        // New: Faculty view their own schedule with relationships
        Route::get('/my-schedule', [FacultyDashboardController::class, 'mySchedule'])->name('schedule.my');
    });
});