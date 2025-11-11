<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
// Faculty routes
Route::resource('faculty', FacultyController::class);

// Schedule routes  
Route::get('schedules/generate', [ScheduleController::class, 'generate'])->name('schedules.generate');

// Conflict routes
Route::post('conflicts/{id}/resolve', [ConflictController::class, 'resolve'])->name('conflicts.resolve');

// Report routes
Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
Route::get('reports/export', [ReportController::class, 'export'])->name('reports.export');
require __DIR__.'/auth.php';
