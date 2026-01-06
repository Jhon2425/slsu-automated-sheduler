<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\FacultyController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Faculty\FacultyDashboardController;
use App\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| Guest Routes (Unauthenticated)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {

    // Landing Page
    Route::get('/', fn () => view('welcome'))->name('welcome');

    // ================= AUTH =================
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    // ================= ADMIN REGISTRATION =================
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

    // ================= LOGOUT =================
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // ================= PROFILE =================
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

        // ================= FACULTY MANAGEMENT =================
        Route::prefix('faculty')->name('faculty.')->group(function () {

            Route::get('/', [FacultyController::class, 'index'])->name('index');
            Route::get('/create', [FacultyController::class, 'create'])->name('create');
            Route::post('/', [FacultyController::class, 'store'])->name('store');

            Route::get('/{faculty}/edit', [FacultyController::class, 'edit'])->name('edit');
            Route::put('/{faculty}', [FacultyController::class, 'update'])->name('update');
            Route::delete('/{faculty}', [FacultyController::class, 'destroy'])->name('destroy');

            Route::get('/{faculty}/subjects', [FacultyController::class, 'getSubjects'])->name('subjects');
            Route::post('/{faculty}/assign-subjects', [FacultyController::class, 'assignSubjects'])->name('assign-subjects');
        });

        // ================= SUBJECT MANAGEMENT =================
        Route::resource('subjects', SubjectController::class)->except(['show']);

        // ================= SCHEDULE MANAGEMENT =================
        Route::prefix('schedules')->name('schedules.')->group(function () {

            Route::get('/', [ScheduleController::class, 'index'])->name('index');
            Route::post('/generate-preview', [ScheduleController::class, 'generatePreview'])->name('generate-preview');
            Route::post('/confirm', [ScheduleController::class, 'confirm'])->name('confirm');

            Route::get('/previous', [ScheduleController::class, 'viewPrevious'])->name('previous');
            Route::get('/calendar-data', [ScheduleController::class, 'getCalendarData'])->name('calendar-data');
            Route::get('/data', [ScheduleController::class, 'getScheduleData'])->name('data');

            Route::get('/print', [ScheduleController::class, 'printSchedule'])->name('print');
            Route::get('/download-pdf', [ScheduleController::class, 'downloadPDF'])->name('download-pdf');

            Route::post('/clear', [ScheduleController::class, 'clearAllSchedules'])->name('clear');

            Route::get('/{id}', [ScheduleController::class, 'show'])->name('show');
        });
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
