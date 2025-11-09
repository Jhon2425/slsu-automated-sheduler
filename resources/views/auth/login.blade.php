<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} - Login</title>
    
    <!-- Load Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Configure Tailwind for custom colors (based on original CSS) -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary-dark': '#0C3B2E', // Original dark green
                        'primary-light': '#6D9773', // Original light green
                        'accent-yellow': '#FFBA00',
                        'accent-brown': '#B46617',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                }
            }
        }
    </script>

    <style>
        /* Base styles and background setup */
        .login-container {
            /* Using a static placeholder image for preview, replace with your asset path */
            background-image: url('https://placehold.co/1920x1080/0C3B2E/ffffff?text=University+Background');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            position: relative;
            overflow: hidden;
            font-family: 'Inter', sans-serif;
        }

        /* Overlay for color grading and better readability */
        .login-container::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(109, 151, 115, 0.85), rgba(12, 59, 46, 0.85));
            z-index: 0;
        }

        /* Responsive fixes for icons in input fields */
        .input-icon-wrapper {
            position: relative;
        }
        .input-icon {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            width: 1.25rem;
            height: 1.25rem;
            pointer-events: none;
            z-index: 10;
        }
    </style>
</head>
<body class="antialiased">
    <div class="login-container min-h-screen flex items-center justify-center p-6">
        
        <!-- Background decorative circles (Tailwind implementation) -->
        <div class="absolute w-96 h-96 opacity-10 rounded-full blur-3xl bg-accent-yellow top-0 right-0"></div>
        <div class="absolute w-96 h-96 opacity-10 rounded-full blur-3xl bg-accent-brown bottom-0 left-0"></div>
        
        <!-- Main login wrapper (Now wider for landscape effect on desktop) -->
        <div class="relative z-10 w-full max-w-md lg:max-w-3xl">
            
            <!-- Login Card - High Transparency Glass Effect, now using p-0 and grid for landscape -->
            <div class="bg-white/20 backdrop-blur-3xl rounded-3xl shadow-2xl border border-white/40 transition-all duration-300 hover:shadow-primary-dark/50 p-0 overflow-hidden">
                
                <div class="grid lg:grid-cols-2">
                    
                    <!-- Left Panel (Branding / Decorative) - Dark background for contrast -->
                    <div class="p-8 md:p-10 text-center bg-primary-dark/70 text-white flex flex-col justify-center items-center">
                        <div class="inline-block p-4 rounded-xl mb-4 bg-gradient-to-br from-primary-light to-primary-dark shadow-lg">
                            <!-- Custom Calendar/Clock SVG Icon -->
                            <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <h1 class="text-3xl font-extrabold mb-2">Welcome Back!</h1>
                        <p class="text-sm font-light opacity-80 max-w-xs">
                            Automated Scheduling Portal for South Luzon State University Tiaong Campus. Please sign in to continue.
                        </p>
                    </div>

                    <!-- Right Panel (Form) - Transparent glass background -->
                    <div class="p-8 md:p-10 space-y-6">

                        <h2 class="text-xl font-bold text-primary-dark mb-4">Sign In to Your Account</h2>

                        <!-- Display session status messages -->
                        @if (session('status'))
                            <div class="mb-4 p-3 bg-teal-100 border border-teal-400 text-teal-800 rounded-lg text-sm">
                                {{ session('status') }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('login') }}" class="flex flex-col gap-5">
                            @csrf

                            <!-- Email/Username input field -->
                            <div>
                                <label for="email" class="block text-sm font-medium text-primary-dark mb-2">Email / Username</label>
                                <div class="input-icon-wrapper">
                                    <!-- Icon for user/email -->
                                    <svg class="input-icon text-primary-light" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    <input 
                                        id="email" 
                                        class="w-full p-3 pl-10 border-2 border-primary-light/30 rounded-xl transition-all duration-200 focus:border-primary-light focus:ring-1 focus:ring-primary-light bg-white/80 @error('email') border-red-500 @enderror" 
                                        type="email" 
                                        name="email" 
                                        value="{{ old('email') }}"
                                        placeholder="Enter your email or username"
                                        required 
                                        autofocus 
                                        autocomplete="username"
                                    >
                                </div>
                                @error('email')
                                    <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Password input field -->
                            <div>
                                <label for="password" class="block text-sm font-medium text-primary-dark mb-2">Password</label>
                                <div class="input-icon-wrapper">
                                    <!-- Icon for password -->
                                    <svg class="input-icon text-primary-light" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                    <input 
                                        id="password" 
                                        class="w-full p-3 pl-10 border-2 border-primary-light/30 rounded-xl transition-all duration-200 focus:border-primary-light focus:ring-1 focus:ring-primary-light bg-white/80 @error('password') border-red-500 @enderror"
                                        type="password"
                                        name="password"
                                        placeholder="Enter your password"
                                        required 
                                        autocomplete="current-password"
                                    >
                                </div>
                                @error('password')
                                    <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Remember me checkbox and forgot password link -->
                            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
                                <label for="remember_me" class="flex items-center cursor-pointer">
                                    <input 
                                        id="remember_me" 
                                        type="checkbox" 
                                        class="w-4 h-4 rounded-md text-primary-dark focus:ring-primary-dark accent-primary-dark cursor-pointer"
                                        name="remember"
                                    >
                                    <span class="ml-2 text-sm text-gray-800 font-semibold">Remember me</span>
                                </label>

                                @if (Route::has('password.request'))
                                    <a class="text-sm font-medium text-accent-brown hover:text-accent-brown/80 transition-colors duration-200" href="{{ route('password.request') }}">
                                        Forgot password?
                                    </a>
                                @endif
                            </div>

                            <!-- Login submit button -->
                            <div>
                                <button type="submit" class="w-full py-3 rounded-xl font-semibold bg-gradient-to-r from-primary-dark to-primary-light text-white shadow-lg transition-all duration-300 hover:shadow-xl hover:shadow-primary-light/40 hover:-translate-y-0.5">
                                    Sign In
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>

            <!-- Copyright footer -->
            <p class="text-center text-xs text-white/80 mt-4">
                © {{ date('Y') }} South Luzon State University - Tiaong Campus
            </p>
        </div>
    </div>
</body>
</html>