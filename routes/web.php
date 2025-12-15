<?php

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
        Route::get('/faculty/{user}/subjects', [FacultyController::class, 'getSubjects'])->name('faculty.subjects');
        Route::post('/faculty/{user}/assign-subjects', [FacultyController::class, 'assignSubjects'])->name('faculty.assign-subjects');

        // Subject Management
        Route::resource('subjects', SubjectController::class)->except(['show']);
        Route::get('/manage-subjects', [SubjectController::class, 'index'])->name('manage-subjects.index');
        Route::post('/manage-subjects', [SubjectController::class, 'store'])->name('manage-subjects.store');
        Route::get('/manage-subjects/create', [SubjectController::class, 'create'])->name('manage-subjects.create');
        Route::put('/manage-subjects/{subject}', [SubjectController::class, 'update'])->name('manage-subjects.update');
        Route::delete('/manage-subjects/{subject}', [SubjectController::class, 'destroy'])->name('manage-subjects.destroy');
        Route::get('/manage-subjects/{subject}/edit', [SubjectController::class, 'edit'])->name('manage-subjects.edit');

        // Program Management
        Route::get('programs', [ProgramController::class, 'index'])->name('programs.index');

        // Enrollment actions
        Route::post('/enrollments/{enrollment}/accept', [ProgramController::class, 'acceptEnrollment'])->name('enrollments.accept');
        Route::post('/enrollments/{enrollment}/decline', [ProgramController::class, 'declineEnrollment'])->name('enrollments.decline');
        Route::get('/enrollments/{enrollment}/edit', [ProgramController::class, 'editEnrollment'])->name('enrollments.edit');
        Route::put('/enrollments/{enrollment}', [ProgramController::class, 'updateEnrollment'])->name('enrollments.update');
        Route::get('/enrollments/{enrollment}/assign-schedule', [ProgramController::class, 'assignSchedule'])->name('enrollments.assign-schedule');
        Route::post('/enrollments/{enrollment}/store-schedule', [ProgramController::class, 'storeSchedule'])->name('enrollments.store-schedule');

        // =================== Schedule Management ===================
        Route::prefix('schedules')->name('schedules.')->group(function () {
            // Main index page
            Route::get('/', [ScheduleController::class, 'index'])->name('index');

            // AJAX schedule generation (for timetable modal)
            Route::post('/generate-preview', [ScheduleController::class, 'generatePreview'])->name('generate-preview');
            Route::post('/confirm', [ScheduleController::class, 'confirm'])->name('confirm');

            // Previous schedules history (SPECIFIC ROUTE - before {id})
            Route::get('/previous', [ScheduleController::class, 'viewPrevious'])->name('previous');

            // Calendar events data (SPECIFIC ROUTE - before {id})
            Route::get('/calendar-data', [ScheduleController::class, 'getCalendarData'])->name('calendar-data');

            // NEW: Schedule data API for AJAX (SPECIFIC ROUTE - before {id})
            Route::get('/data', [ScheduleController::class, 'getScheduleData'])->name('data');

            // Export & print (SPECIFIC ROUTES - before {id})
            Route::get('/print', [ScheduleController::class, 'printSchedule'])->name('print');
            Route::get('/download-pdf', [ScheduleController::class, 'downloadPDF'])->name('download-pdf');
            Route::get('/download-excel', [ScheduleController::class, 'downloadExcel'])->name('download-excel');

            // Clear all schedules
            Route::post('/clear', [ScheduleController::class, 'clearAllSchedules'])->name('clear');

            // Legacy compatibility routes
            Route::post('/generate', [ScheduleController::class, 'generatePreview'])->name('generate');
            Route::post('/save', [ScheduleController::class, 'confirm'])->name('save');
            Route::get('/download', [ScheduleController::class, 'downloadPDF'])->name('download');

            // Single schedule view (modal) - MUST BE LAST
            Route::get('/{id}', [ScheduleController::class, 'show'])->name('show');
        });
    });

    // =================== Faculty Routes ===================
    Route::prefix('faculty')->middleware('faculty')->name('faculty.')->group(function () {
        Route::get('/dashboard', [FacultyDashboardController::class, 'index'])->name('dashboard');

        // Program enrollment
        Route::post('/programs/{program}/enroll', [FacultyDashboardController::class, 'enrollProgram'])->name('programs.enroll');
        Route::delete('/enrollments/{enrollment}/unenroll', [FacultyDashboardController::class, 'unenrollProgram'])->name('programs.unenroll');

        // Schedule viewing (SPECIFIC ROUTES - before any {id} routes)
        Route::get('/schedule/download-pdf', [FacultyDashboardController::class, 'downloadPDF'])->name('schedule.download-legacy');
        Route::get('/my-schedule', [FacultyDashboardController::class, 'mySchedule'])->name('schedule.my');

        // Schedule viewing with enrollment ID
        Route::get('/enrollments/{enrollment}/schedule', [FacultyDashboardController::class, 'viewSchedule'])->name('schedule.view');
        Route::get('/enrollments/{enrollment}/schedule/download', [FacultyDashboardController::class, 'downloadSchedule'])->name('schedule.download');
    });
});