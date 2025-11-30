<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Models\Schedule;
use App\Models\Classroom;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $totalFaculties = Faculty::count();
        $totalSchedules = Schedule::count();
        $totalClassrooms = Classroom::count();

        return view('admin.dashboard', compact('totalFaculties', 'totalSchedules', 'totalClassrooms'));
    }
}

