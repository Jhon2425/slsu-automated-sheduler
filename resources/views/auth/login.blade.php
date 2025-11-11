<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'SLSU') }} - Login</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary-dark': '#0C3B2E',
                        'primary-light': '#6D9773',
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
        /* Glassmorphism Card */
        .login-card {
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-radius: 20px;
            box-shadow: 0 8px 40px rgba(0, 0, 0, 0.25);
            overflow: hidden;
        }

        /* Glass Button */
        .glass-button {
            width: 100%;
            padding: 0.9rem;
            font-weight: 600;
            border-radius: 0.75rem;
            border: 1px solid rgba(255, 255, 255, 0.3);
            background: rgba(255, 255, 255, 0.15);
            color: white;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
        }

        .glass-button:hover {
            background: linear-gradient(90deg, #0C3B2E 0%, #6D9773 100%);
            border-color: transparent;
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(12, 59, 46, 0.4);
        }

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
<body class="antialiased font-sans">

<div class="login-container relative min-h-screen flex items-center justify-center overflow-hidden">

    <!-- Background Image -->
    <img src="{{ asset('automated.png') }}" alt="Background"
         class="absolute top-0 left-0 w-full h-full object-cover z-0">

    <!-- Overlay -->
    <div class="absolute inset-0 bg-gradient-to-br from-primary-dark/70 to-primary-light/60 z-10"></div>

    <!-- Login Card -->
    <div class="login-card relative z-20 w-full max-w-md lg:max-w-3xl p-0">
        <div class="grid lg:grid-cols-2">

            <!-- Left Side -->
            <div class="p-8 md:p-10 text-center bg-primary-dark/70 text-white flex flex-col justify-center items-center">
               <div class="inline-block p-4 mb-4 rounded-full bg-gradient-to-br from-primary-light to-primary-dark shadow-lg">
                    <img src="{{ asset('slsu-logo.png') }}" alt="SLSU Logo" class="w-16 h-16 object-contain rounded-full">
                </div>

                <h1 class="text-3xl font-extrabold mb-2">Welcome Back!</h1>
                <p class="text-sm font-light opacity-80 max-w-xs">
                    Automated Scheduling Portal for South Luzon State University Tiaong Campus. Please sign in to continue.
                </p>
            </div>

            <!-- Right Side (Form) -->
            <div class="p-8 md:p-10 space-y-6 bg-white/10 backdrop-blur-lg">
                <h2 class="text-xl font-bold text-primary-dark mb-4">Sign In to Your Account</h2>

                @if (session('status'))
                    <div class="mb-4 p-3 bg-teal-100 border border-teal-400 text-teal-800 rounded-lg text-sm">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="flex flex-col gap-5">
                    @csrf

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-primary-dark mb-2">Email / Username</label>
                        <div class="input-icon-wrapper">
                            <svg class="input-icon text-primary-light" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <input id="email" class="w-full p-3 pl-10 border-2 border-primary-light/30 rounded-xl focus:border-primary-light focus:ring-1 focus:ring-primary-light bg-white/70" type="email" name="email" placeholder="Enter your email or username" required autofocus>
                        </div>
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-primary-dark mb-2">Password</label>
                        <div class="input-icon-wrapper">
                            <svg class="input-icon text-primary-light" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            <input id="password" class="w-full p-3 pl-10 border-2 border-primary-light/30 rounded-xl focus:border-primary-light focus:ring-1 focus:ring-primary-light bg-white/70" type="password" name="password" placeholder="Enter your password" required>
                        </div>
                    </div>

                    <!-- Remember + Forgot -->
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
                        <label class="flex items-center">
                            <input type="checkbox" class="w-4 h-4 rounded-md text-primary-dark focus:ring-primary-dark accent-primary-dark cursor-pointer">
                            <span class="ml-2 text-sm text-gray-800 font-semibold">Remember me</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a class="text-sm font-medium text-accent-brown hover:text-accent-brown/80 transition-colors" href="{{ route('password.request') }}">
                                Forgot password?
                            </a>
                        @endif
                    </div>

                    <!-- Glass Button -->
                    <button type="submit" class="glass-button">
                        Sign In
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <p class="absolute bottom-4 text-center text-xs text-white/80 z-20">
        © {{ date('Y') }} South Luzon State University - Tiaong Campus
    </p>
</div>

</body>
</html>
