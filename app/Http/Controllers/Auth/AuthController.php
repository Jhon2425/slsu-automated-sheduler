<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Program;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class AuthController extends Controller
{
    // Show login form
    public function showLogin(): View
    {
        return view('auth.login');
    }

    // Handle login
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();
            if ($user->isAdmin()) {
                return redirect()->route('admin.dashboard');
            }

            return redirect()->route('faculty.dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    // Show admin registration form
    public function showRegisterAdmin(): View
    {
        return view('auth.register-admin');
    }

    // Handle admin registration + auto-create program
    public function registerAdmin(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'program' => ['required', 'string', 'max:255', 'unique:programs,name'],
        ]);

        $adminRole = Role::where('name', 'admin')->firstOrFail();

        try {
            DB::beginTransaction();

            // Create admin user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role_id' => $adminRole->id,
                // if your users table doesn't have a 'program' column, remove this
                'program' => $request->program,
            ]);

            // Create program
            Program::create([
                'name' => $request->program,
                'code' => strtoupper(substr(preg_replace('/\s+/', '', $request->program), 0, 6)), // safer short code
            ]);

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            // Log if you have logging, e.g. \Log::error($e);
            return back()->withInput()->withErrors(['error' => 'Unable to create admin and program.']);
        }

        event(new Registered($user));
        Auth::login($user);

        return redirect()->route('admin.dashboard')->with('success', 'Admin and program created successfully!');
    }

    // Show faculty registration form
    public function showRegisterFaculty(): View
    {
        return view('auth.register-faculty');
    }

    // Handle faculty registration
    public function registerFaculty(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'faculty_id' => ['required', 'string', 'max:255', Rule::unique('users', 'faculty_id')],
        ], [
            'faculty_id.unique' => 'This faculty ID is already registered.',
        ]);

        $facultyRole = Role::where('name', 'faculty')->firstOrFail();

        try {
            DB::beginTransaction();

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role_id' => $facultyRole->id,
                'faculty_id' => $request->faculty_id,
            ]);

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            // Log if you have logging, e.g. \Log::error($e);
            return back()->withInput()->withErrors(['error' => 'Unable to register faculty.']);
        }

        event(new Registered($user));
        Auth::login($user);

        return redirect()->route('faculty.dashboard')->with('success', 'Faculty registered successfully!');
    }

    // Handle logout
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
