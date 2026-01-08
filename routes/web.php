<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ProfileController;

// ================= ADMIN CONTROLLERS =================
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\FacultyController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Admin\ExaminationController;
use App\Http\Controllers\Admin\ExamScheduleController;
use App\Http\Controllers\Admin\ArchiveController;

// ================= FACULTY CONTROLLERS =================
use App\Http\Controllers\Faculty\FacultyDashboardController;

/*
|--------------------------------------------------------------------------
| Guest Routes (Unauthenticated)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {

    // Landing Page
    Route::get('/', fn () => view('welcome'))->name('welcome');

    // Authentication
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    // Admin Registration
    Route::get('/register/admin', [AuthController::class, 'showRegisterAdmin'])
        ->name('register.admin');

    Route::post('/register/admin', [AuthController::class, 'registerAdmin']);
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Profile & Settings
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/settings', fn () => view('settings'))->name('settings');

    /*
    |--------------------------------------------------------------------------
    | Admin Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('admin')
        ->middleware('admin')
        ->name('admin.')
        ->group(function () {

        // Dashboard
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])
            ->name('dashboard');

        /*
        |---------------- FACULTY MANAGEMENT ----------------
        */
        Route::prefix('faculty')->name('faculty.')->group(function () {

            Route::get('/', [FacultyController::class, 'index'])->name('index');
            Route::get('/create', [FacultyController::class, 'create'])->name('create');
            Route::post('/', [FacultyController::class, 'store'])->name('store');

            Route::get('/{faculty}/edit', [FacultyController::class, 'edit'])->name('edit');
            Route::put('/{faculty}', [FacultyController::class, 'update'])->name('update');
            Route::delete('/{faculty}', [FacultyController::class, 'destroy'])->name('destroy');

            Route::get('/{faculty}/subjects', [FacultyController::class, 'getSubjects'])
                ->name('subjects');

            Route::post('/{faculty}/assign-subjects', [FacultyController::class, 'assignSubjects'])
                ->name('assign-subjects');
        });

        /*
        |---------------- EXAM SCHEDULE MANAGEMENT ----------------
        | Manual exam schedule creation and management
        */
            Route::prefix('exam-schedules')->name('exam-schedules.')->group(function () {
                Route::get('/', [ExaminationController::class, 'index'])->name('index');
                Route::get('/create', [ExaminationController::class, 'create'])->name('create');
                Route::post('/', [ExaminationController::class, 'store'])->name('store');
                Route::get('/{examSchedule}/edit', [ExaminationController::class, 'edit'])->name('edit');
                Route::put('/{examSchedule}', [ExaminationController::class, 'update'])->name('update');
                Route::delete('/{examSchedule}', [ExaminationController::class, 'destroy'])->name('destroy');
            });

        /*
        |---------------- CLASS SCHEDULE MANAGEMENT ----------------
        */
        Route::prefix('schedules')->name('schedules.')->group(function () {

            Route::get('/', [ScheduleController::class, 'index'])->name('index');

            Route::post('/generate-preview', [ScheduleController::class, 'generatePreview'])
                ->name('generate-preview');

            Route::post('/confirm', [ScheduleController::class, 'confirm'])
                ->name('confirm');

            Route::get('/previous', [ScheduleController::class, 'viewPrevious'])
                ->name('previous');

            Route::get('/calendar-data', [ScheduleController::class, 'getCalendarData'])
                ->name('calendar-data');

            Route::get('/data', [ScheduleController::class, 'getScheduleData'])
                ->name('data');

            Route::get('/print', [ScheduleController::class, 'printSchedule'])
                ->name('print');

            Route::get('/download-pdf', [ScheduleController::class, 'downloadPDF'])
                ->name('download-pdf');

            Route::get('/download-excel', [ScheduleController::class, 'downloadExcel'])
                ->name('download-excel');

            Route::post('/clear', [ScheduleController::class, 'clearAllSchedules'])
                ->name('clear');

            Route::get('/{id}', [ScheduleController::class, 'show'])
                ->name('show');
        });

        /*
        |---------------- EXAMINATION SCHEDULE MANAGEMENT ----------------
        | Auto-generated examination schedules (from scheduler)
        */
        Route::prefix('examinations')->name('examinations.')->group(function () {

            Route::get('/', [ExaminationController::class, 'index'])->name('index');

            Route::post('/generate-preview', [ExaminationController::class, 'generatePreview'])
                ->name('generate-preview');

            Route::post('/confirm', [ExaminationController::class, 'confirm'])
                ->name('confirm');

            Route::get('/previous', [ExaminationController::class, 'viewPrevious'])
                ->name('previous');

            Route::get('/calendar-data', [ExaminationController::class, 'getCalendarData'])
                ->name('calendar-data');

            Route::get('/data', [ExaminationController::class, 'getExaminationData'])
                ->name('data');

            Route::get('/print', [ExaminationController::class, 'printExamination'])
                ->name('print');

            Route::get('/download-pdf', [ExaminationController::class, 'downloadPDF'])
                ->name('download-pdf');

            Route::get('/download-excel', [ExaminationController::class, 'downloadExcel'])
                ->name('download-excel');

            Route::post('/clear', [ExaminationController::class, 'clearAllExaminations'])
                ->name('clear');

            Route::get('/{id}', [ExaminationController::class, 'show'])
                ->name('show');
        });

        /*
        |---------------- VIEW GENERATED ARCHIVES ----------------
        | View all previously generated schedules and examinations
        */
        Route::get('/view-generated-archives', [ArchiveController::class, 'index'])
            ->name('archives.index');
    });

    /*
    |--------------------------------------------------------------------------
    | Faculty Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('faculty')
        ->middleware('faculty')
        ->name('faculty.')
        ->group(function () {

        Route::get('/dashboard', [FacultyDashboardController::class, 'index'])
            ->name('dashboard');

        Route::get('/my-schedule', [FacultyDashboardController::class, 'mySchedule'])
            ->name('schedule.my');

        Route::get('/schedule/view', [FacultyDashboardController::class, 'viewSchedule'])
            ->name('schedule.view');

        Route::get('/schedule/download', [FacultyDashboardController::class, 'downloadSchedule'])
            ->name('schedule.download');

        Route::get('/schedule/download-pdf', [FacultyDashboardController::class, 'downloadPDF'])
            ->name('schedule.download-legacy');
    });
});