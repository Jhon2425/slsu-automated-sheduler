<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Program;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(Request $request): View
    {
        $role = $request->query('role'); // Get role from URL
        return view('auth.register', compact('role'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:admin,faculty'], // Validate role
            'program' => $request->role === 'admin' ? ['required', 'string', 'max:255'] : [], // Program required for admin
        ]);

        $program = null;
        if ($request->role === 'admin') {
            // Create or get existing program
            $program = Program::firstOrCreate(
                ['name' => $request->program],
                [
                    'code' => strtoupper(substr($request->program, 0, 4)),
                    'description' => 'Created during admin registration',
                ]
            );
        }

        // Create the user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'program' => $program?->name,   // text field in users table
            'program_id' => $program?->id,  // optional foreign key
        ]);

        event(new Registered($user));
        Auth::login($user);

        return redirect(route('dashboard'));
    }
}
