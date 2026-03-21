<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <link rel="icon" type="image/png" href="{{ asset('images/slsu-logo.png') }}?v=3">
    <link rel="icon" type="image/png" href="{{ asset('images/slsu-logo.png') }}?v=1">
    <link rel="apple-touch-icon" href="{{ asset('images/slsu-logo.png') }}">

    <title>SLSU - Admin Registration</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary-dark':  '#0C3B2E',
                        'primary-light': '#6D9773',
                        'accent-yellow': '#FFBA00',
                        'accent-brown':  '#B46617',
                    }
                }
            }
        }
    </script>

    <style>
        @keyframes slideInRight {
            from { opacity: 0; transform: translateX(50px); }
            to   { opacity: 1; transform: translateX(0); }
        }
        @keyframes slideInLeft {
            from { opacity: 0; transform: translateX(-50px); }
            to   { opacity: 1; transform: translateX(0); }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to   { opacity: 1; }
        }

        .login-card {
            background: rgba(255,255,255,0.15);
            border: 1px solid rgba(255,255,255,0.15);
            backdrop-filter: blur(12px);
            border-radius: 24px;
            box-shadow: 0 8px 40px rgba(0,0,0,0.25);
        }

        .step-content { animation: slideInRight 0.4s ease-out; }
        .step-content.reverse { animation: slideInLeft 0.4s ease-out; }

        .glass-button {
            width: 100%;
            display: flex; align-items: center; justify-content: center;
            height: 58px; padding: 0 1rem;
            font-weight: 600; border-radius: 0.75rem;
            background: rgba(255,255,255,0.15);
            border: 1.5px solid rgba(255,255,255,0.35);
            backdrop-filter: blur(12px);
            color: white; cursor: pointer;
            transition: all 0.3s ease;
            position: relative; overflow: hidden;
        }
        .glass-button::before {
            content: '';
            position: absolute; top: 0; left: -100%;
            width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.6s ease;
        }
        .glass-button:hover::before { left: 100%; }
        .glass-button span { line-height: 1.1; text-align: center; }
        .glass-button:hover:not(:disabled) {
            background: linear-gradient(90deg, #0C3B2E 0%, #6D9773 100%);
            border: none; backdrop-filter: none;
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(12,59,46,0.4);
        }
        .glass-button:disabled { opacity: 0.5; cursor: not-allowed; }

        .progress-bar {
            height: 6px; background: rgba(255,255,255,0.2);
            border-radius: 10px; overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #FFBA00 0%, #B46617 100%);
            border-radius: 10px;
            transition: width 0.5s cubic-bezier(0.4,0,0.2,1);
            box-shadow: 0 0 10px rgba(255,186,0,0.5);
        }

        .step-indicator {
            width: 40px; height: 40px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            background: rgba(255,255,255,0.2);
            border: 2px solid rgba(255,255,255,0.3);
            color: white; font-weight: 600;
            transition: all 0.3s ease;
        }
        .step-indicator.active {
            background: linear-gradient(135deg, #FFBA00 0%, #B46617 100%);
            border-color: #FFBA00;
            box-shadow: 0 0 20px rgba(255,186,0,0.6);
            transform: scale(1.1);
        }
        .step-indicator.completed { background: #6D9773; border-color: #6D9773; }

        .step-line {
            height: 2px; background: rgba(255,255,255,0.2);
            flex: 1; margin: 0 8px; position: relative; overflow: hidden;
        }
        .step-line.completed::after {
            content: '';
            position: absolute; left: 0; top: 0;
            height: 100%; width: 100%;
            background: #6D9773;
            animation: fillLine 0.5s ease-out;
        }
        @keyframes fillLine { from { width: 0; } to { width: 100%; } }

        .input-wrapper {
            position: relative;
            animation: fadeIn 0.45s ease-out backwards;
        }

        /* The wrapper that holds the input + icons is only position:relative */
        .input-icon-wrapper { position: relative; }

        .input-left {
            position: absolute; left: 1rem; top: 50%;
            transform: translateY(-50%);
            width: 1.25rem; height: 1.25rem;
            pointer-events: none; z-index: 10;
            color: inherit;
        }

        .input-icon {
            position: absolute; right: 1rem; top: 50%;
            transform: translateY(-50%);
            opacity: 0; transition: all 0.25s ease;
            z-index: 12; pointer-events: none;
        }
        .input-icon svg {
            stroke: #6D9773 !important;
            stroke-width: 2.8 !important;
        }
        .input-icon.show { opacity: 1; }

        /* hide neon-check for password fields — eye toggle is sufficient */
        #password ~ .input-icon,
        #password_confirmation ~ .input-icon { display: none !important; }

        .enhanced-input {
            width: 100%;
            padding: 1rem 3rem 1rem 3rem;
            border: 2px solid rgba(109,151,115,0.3);
            border-radius: 0.875rem;
            background: rgba(255,255,255,0.85);
            transition: all 0.3s cubic-bezier(0.4,0,0.2,1);
            font-size: 0.95rem;
        }
        .enhanced-input:focus {
            outline: none;
            border-color: #6D9773;
            background: rgba(255,255,255,0.95);
            box-shadow: 0 0 0 4px rgba(109,151,115,0.15), 0 8px 20px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        /* green border = all rules pass */
        .enhanced-input.valid {
            border-color: #6D9773 !important;
            box-shadow: 0 0 0 3px rgba(109,151,115,0.2);
        }
        /* red border = typed but invalid */
        .enhanced-input.invalid {
            border-color: #ef4444 !important;
            box-shadow: 0 0 0 3px rgba(239,68,68,0.15);
        }

        .toggle-btn {
            position: absolute; right: 1rem; top: 50%;
            transform: translateY(-50%);
            background: transparent; border: none;
            padding: 0.35rem;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; z-index: 11;
            color: rgba(12,59,46,0.7);
        }
        .toggle-btn:hover { color: rgba(12,59,46,1); }

        .shake { animation: shake 0.5s; }
        @keyframes shake {
            0%,100% { transform: translateX(0); }
            25%      { transform: translateX(-10px); }
            75%      { transform: translateX(10px); }
        }

        .floating { animation: floating 3s ease-in-out infinite; }
        @keyframes floating {
            0%,100% { transform: translateY(0); }
            50%      { transform: translateY(-10px); }
        }

        /* ══════════════════════════════════════════
           Password requirements checklist
           — sits in normal document flow, BELOW the
             .input-icon-wrapper, never overlapping it
        ══════════════════════════════════════════ */
        #password-requirements {
            margin-top: 0.625rem;   /* gap below the input */
        }

        .req-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.75rem;
            color: rgba(255,255,255,0.55);
            transition: color 0.2s ease;
            line-height: 1.5;
        }
        .req-item.met { color: rgba(255,255,255,0.95); }

        .req-icon {
            flex-shrink: 0;
            width: 1.1rem; height: 1.1rem;
            border-radius: 50%;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 9px; font-weight: 700;
            border: 1.5px solid rgba(255,90,90,0.6);
            color: rgba(255,90,90,0.9);
            transition: background 0.2s ease, border-color 0.2s ease, color 0.2s ease;
        }
        .req-item.met .req-icon {
            background: #6D9773;
            border-color: #6D9773;
            color: #fff;
        }

        /* confirm-match message below confirm input */
        #confirm-match-msg {
            font-size: 0.75rem;
            margin-top: 0.375rem;
            min-height: 1.1rem;     /* reserve space so layout doesn't jump */
            display: block;
        }
        #confirm-match-msg.ok  { color: #6ee7b7; }
        #confirm-match-msg.err { color: #fca5a5; }
    </style>
</head>
<body class="antialiased font-sans">
<div class="relative min-h-screen flex items-center justify-center overflow-hidden">
    <img src="{{ asset('automated.png') }}" alt="Background" class="absolute inset-0 w-full h-full object-cover z-0">
    <div class="absolute inset-0 bg-gradient-to-br from-primary-dark/70 to-primary-light/60 z-10"></div>

    <div class="login-card relative z-20 w-full max-w-md lg:max-w-3xl">
        <div class="grid lg:grid-cols-2">

            <!-- ══ Left Panel ══ -->
            <div class="p-8 md:p-10 text-center bg-primary-dark/70 text-white flex flex-col justify-center items-center rounded-l-3xl">
                <div class="inline-block p-4 mb-4 rounded-full bg-gradient-to-br from-primary-light to-primary-dark shadow-lg floating">
                    <img src="{{ asset('slsu-logo.png') }}" alt="SLSU Logo" class="w-16 h-16 object-contain rounded-full">
                </div>
                <h1 class="text-3xl font-extrabold mb-2">Admin Registration</h1>
                <p class="text-sm font-light opacity-80 max-w-xs">
                    Create an admin account to manage programs, faculty, and schedules.
                </p>
                <div class="mt-6 flex items-center gap-2 text-accent-yellow">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <span class="text-sm font-semibold">Administrator Access</span>
                </div>

                <!-- Step dots -->
                <div class="mt-8 w-full max-w-sm">
                    <div class="flex items-center justify-center mb-4">
                        <div class="step-indicator active" id="step-1-indicator">1</div>
                        <div class="step-line" id="line-1"></div>
                        <div class="step-indicator" id="step-2-indicator">2</div>
                        <div class="step-line" id="line-2"></div>
                        <div class="step-indicator" id="step-3-indicator">3</div>
                    </div>
                    <div class="text-sm opacity-80" id="step-label">Personal Information</div>
                </div>

                <!-- Progress bar -->
                <div class="w-full max-w-sm mt-4">
                    <div class="progress-bar">
                        <div class="progress-fill" id="progress-fill" style="width:33.33%"></div>
                    </div>
                    <p class="text-xs mt-2 opacity-70" id="progress-text">Step 1 of 3</p>
                </div>
            </div>

            <!-- ══ Right Panel (Form) ══ -->
            <div class="p-8 md:p-10 bg-white/10 backdrop-blur-lg rounded-r-3xl relative overflow-hidden min-h-[600px] flex flex-col justify-between">
                <form method="POST" action="{{ route('register.admin') }}" id="registration-form">
                    @csrf

                    @if ($errors->any())
                    <div class="mb-2 p-3 bg-red-100 border border-red-400 text-red-800 rounded-lg text-sm">
                        <ul class="list-disc list-inside m-0 p-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <!-- ── Step 1: Personal Information ── -->
                    <div class="step-content" id="step-1">
                        <h2 class="text-xl font-bold text-primary-dark mb-6">Personal Information</h2>
                        <div class="space-y-5">

                            <div class="input-wrapper">
                                <label for="name" class="block text-sm font-semibold text-primary-dark mb-2">Full Name</label>
                                <div class="input-icon-wrapper">
                                    <svg class="input-left text-primary-light" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    <input id="name" class="enhanced-input" type="text" name="name"
                                           value="{{ old('name') }}" placeholder="Enter your full name" required>
                                    <div class="input-icon" aria-hidden="true">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                </div>
                                @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div class="input-wrapper">
                                <label for="email" class="block text-sm font-semibold text-primary-dark mb-2">Email Address</label>
                                <div class="input-icon-wrapper">
                                    <svg class="input-left text-primary-light" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                    <input id="email" class="enhanced-input" type="email" name="email"
                                           value="{{ old('email') }}" placeholder="yourname@slsu.edu.ph" required>
                                    <div class="input-icon" aria-hidden="true">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                </div>
                                @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                        </div>
                    </div>

                    <!-- ── Step 2: Program Details ── -->
                    <div class="step-content hidden" id="step-2">
                        <h2 class="text-xl font-bold text-primary-dark mb-6">Program Details</h2>
                        <div class="space-y-5">

                            <div class="input-wrapper">
                                <label for="program" class="block text-sm font-semibold text-primary-dark mb-2">Program</label>
                                <div class="input-icon-wrapper">
                                    <svg class="input-left text-primary-light" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                    </svg>
                                    <input id="program" class="enhanced-input" type="text" name="program"
                                           value="{{ old('program') }}" placeholder="e.g., BSIT, BSCS, BSBA" required>
                                    <div class="input-icon" aria-hidden="true">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                </div>
                                <p class="text-xs text-white/70 mt-2">Enter the program code you will manage.</p>
                                @error('program') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                        </div>
                    </div>

                    <!-- ── Step 3: Security ── -->
                    <div class="step-content hidden" id="step-3">
                        <h2 class="text-xl font-bold text-primary-dark mb-6">Security</h2>
                        <div class="space-y-5">

                            <!-- Password -->
                            <div class="input-wrapper">
                                <label for="password" class="block text-sm font-semibold text-primary-dark mb-2">Password</label>

                                {{-- Input row --}}
                                <div class="input-icon-wrapper">
                                    <svg class="input-left text-primary-light" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                    <input id="password" class="enhanced-input pr-12" type="password"
                                           name="password" placeholder="Enter your password" required>
                                    <button type="button" class="toggle-btn"
                                            onclick="togglePassword('password')"
                                            aria-label="Toggle password visibility">
                                        <!-- eye-open (shown when password is hidden) -->
                                        <svg class="eye-open w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        <!-- eye-off (shown when password is visible) -->
                                        <svg class="eye-off w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a10.05 10.05 0 013.042-4.568M6.18 6.18A9.956 9.956 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.965 9.965 0 01-2.304 3.868M3 3l18 18"/>
                                        </svg>
                                    </button>
                                    <div class="input-icon" aria-hidden="true">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                </div>

                                @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror

                                {{--
                                    ✅ Requirements checklist
                                    Lives in NORMAL FLOW below .input-icon-wrapper.
                                    Hidden until user starts typing.
                                --}}
                                <div id="password-requirements"
                                     class="hidden p-3 rounded-xl bg-white/10 backdrop-blur-sm border border-white/20">
                                    <p class="text-xs font-semibold text-white/80 mb-2 uppercase tracking-wide">
                                        Password must contain:
                                    </p>
                                    <ul class="space-y-1.5">
                                        <li class="req-item" id="req-length">
                                            <span class="req-icon">✕</span>At least 8 characters
                                        </li>
                                        <li class="req-item" id="req-upper">
                                            <span class="req-icon">✕</span>One uppercase letter (A–Z)
                                        </li>
                                        <li class="req-item" id="req-lower">
                                            <span class="req-icon">✕</span>One lowercase letter (a–z)
                                        </li>
                                        <li class="req-item" id="req-number">
                                            <span class="req-icon">✕</span>One number (0–9)
                                        </li>
                                        <li class="req-item" id="req-special">
                                            <span class="req-icon">✕</span>One special character (!@#$%^&amp;*)
                                        </li>
                                    </ul>
                                </div>

                                {{-- Strength bar --}}
                                <div id="password-strength" class="hidden mt-2">
                                    <div class="flex gap-1 mb-1">
                                        <div class="h-1 flex-1 rounded bg-white/20" id="strength-1"></div>
                                        <div class="h-1 flex-1 rounded bg-white/20" id="strength-2"></div>
                                        <div class="h-1 flex-1 rounded bg-white/20" id="strength-3"></div>
                                        <div class="h-1 flex-1 rounded bg-white/20" id="strength-4"></div>
                                    </div>
                                    <p class="text-xs text-white/80" id="strength-text">Strength: —</p>
                                </div>
                            </div>

                            <!-- Confirm Password -->
                            <div class="input-wrapper">
                                <label for="password_confirmation"
                                       class="block text-sm font-semibold text-primary-dark mb-2">Confirm Password</label>
                                <div class="input-icon-wrapper">
                                    <svg class="input-left text-primary-light" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                    <input id="password_confirmation" class="enhanced-input pr-12" type="password"
                                           name="password_confirmation" placeholder="Re-enter your password" required>
                                    <button type="button" class="toggle-btn"
                                            onclick="togglePassword('password_confirmation')"
                                            aria-label="Toggle confirm password visibility">
                                        <svg class="eye-open w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        <svg class="eye-off w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a10.05 10.05 0 013.042-4.568M6.18 6.18A9.956 9.956 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.965 9.965 0 01-2.304 3.868M3 3l18 18"/>
                                        </svg>
                                    </button>
                                    <div class="input-icon" aria-hidden="true">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                </div>
                                @error('password_confirmation') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror

                                {{-- ✅ Live match message — BELOW the confirm input, in normal flow --}}
                                <p id="confirm-match-msg" aria-live="polite"></p>
                            </div>

                        </div>
                    </div>

                    <!-- ── Navigation Buttons ── -->
                    <div class="flex gap-3 mt-8">
                        <button type="button" id="prev-btn" class="glass-button hidden">
                            <span class="flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                </svg>
                                Previous
                            </span>
                        </button>
                        <button type="button" id="next-btn" class="glass-button">
                            <span class="flex items-center justify-center gap-2">
                                Next
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </span>
                        </button>
                        <button type="submit" id="submit-btn" class="glass-button hidden">
                            <span class="flex items-center justify-center gap-2">
                                Complete Registration
                            </span>
                        </button>
                    </div>

                    <div class="mt-12">
                        <p class="text-sm text-white text-center">
                            Already have an account?
                            <a href="{{ route('login') }}" class="text-[#FFBA00] font-semibold transition">Sign In</a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <p class="absolute bottom-4 left-1/2 -translate-x-1/2 text-center text-xs text-white/80 z-20">
        © {{ date('Y') }} South Luzon State University - Tiaong Campus
    </p>
</div>

<script>
/* ═══════════════════════════════════════════════
   State
═══════════════════════════════════════════════ */
let currentStep = 1;
const totalSteps = 3;
let isReverse = false;
const stepLabels = { 1: 'Personal Information', 2: 'Program Details', 3: 'Security' };

/* ═══════════════════════════════════════════════
   DOM refs
═══════════════════════════════════════════════ */
const nameInput            = document.getElementById('name');
const emailInput           = document.getElementById('email');
const programInput         = document.getElementById('program');
const passwordInput        = document.getElementById('password');
const passwordConfirmInput = document.getElementById('password_confirmation');
const confirmMatchMsg      = document.getElementById('confirm-match-msg');

/* ═══════════════════════════════════════════════
   Password requirement rules
═══════════════════════════════════════════════ */
const RULES = {
    'req-length':  v => v.length >= 8,
    'req-upper':   v => /[A-Z]/.test(v),
    'req-lower':   v => /[a-z]/.test(v),
    'req-number':  v => /[0-9]/.test(v),
    'req-special': v => /[^a-zA-Z0-9]/.test(v),
};

/** Evaluate all rules, update each checklist row, return true when all pass. */
function checkPasswordRequirements(value) {
    const box = document.getElementById('password-requirements');

    if (value.length === 0) {
        box.classList.add('hidden');
        passwordInput.classList.remove('valid', 'invalid');
        return false;
    }

    box.classList.remove('hidden');

    let allMet = true;
    for (const [id, test] of Object.entries(RULES)) {
        const item = document.getElementById(id);
        const icon = item.querySelector('.req-icon');
        const met  = test(value);
        item.classList.toggle('met', met);
        icon.textContent = met ? '✓' : '✕';
        if (!met) allMet = false;
    }

    passwordInput.classList.toggle('valid',   allMet);
    passwordInput.classList.toggle('invalid', !allMet);

    return allMet;
}

/** True only when every rule passes. */
function validatePasswordRules(v) {
    return v.length > 0 && Object.values(RULES).every(test => test(v));
}

/* ═══════════════════════════════════════════════
   Confirm match check — updates message below input
═══════════════════════════════════════════════ */
function checkConfirmMatch() {
    const pw  = passwordInput.value;
    const cfm = passwordConfirmInput.value;

    if (cfm.length === 0) {
        confirmMatchMsg.className = '';
        confirmMatchMsg.textContent = '';
        passwordConfirmInput.classList.remove('valid', 'invalid');
        return false;
    }

    const pwOk   = validatePasswordRules(pw);
    const matches = pw === cfm;

    if (pwOk && matches) {
        confirmMatchMsg.className   = 'ok';
        confirmMatchMsg.textContent = '✓ Passwords match';
        passwordConfirmInput.classList.add('valid');
        passwordConfirmInput.classList.remove('invalid');
        return true;
    }

    confirmMatchMsg.className   = 'err';
    confirmMatchMsg.textContent = matches
        ? '✗ Password does not meet requirements'
        : '✗ Passwords do not match';
    passwordConfirmInput.classList.add('invalid');
    passwordConfirmInput.classList.remove('valid');
    return false;
}

/* ═══════════════════════════════════════════════
   Strength bar
═══════════════════════════════════════════════ */
function updateStrengthBar(value) {
    const div  = document.getElementById('password-strength');
    const text = document.getElementById('strength-text');
    const bars = [1,2,3,4].map(i => document.getElementById(`strength-${i}`));

    if (value.length === 0) { div.classList.add('hidden'); return; }
    div.classList.remove('hidden');

    let score = 0;
    if (value.length >= 8)                           score++;
    if (/[a-z]/.test(value) && /[A-Z]/.test(value)) score++;
    if (/[0-9]/.test(value))                         score++;
    if (/[^a-zA-Z0-9]/.test(value))                 score++;

    const colors = ['bg-red-500','bg-orange-400','bg-yellow-400','bg-green-500'];
    const labels = ['Weak','Fair','Good','Strong'];

    bars.forEach((bar, i) => {
        bar.className = 'h-1 flex-1 rounded bg-white/20';
        if (i < score) bar.classList.add(colors[score - 1]);
    });
    text.textContent = `Strength: ${labels[score - 1] ?? 'Weak'}`;
}

/* ═══════════════════════════════════════════════
   Generic field validation (for steps 1 & 2)
═══════════════════════════════════════════════ */
function validateField(input) {
    if (!input) return false;
    if (input.id === 'email')
        return /^[a-zA-Z0-9._%+\-]+@slsu\.edu\.ph$/.test(input.value);
    if (input.id === 'password')
        return validatePasswordRules(input.value);
    if (input.id === 'password_confirmation')
        return input.value === passwordInput.value && validatePasswordRules(passwordInput.value);
    return input.value.trim() !== '';
}

/* ═══════════════════════════════════════════════
   Generic listeners for name / email / program
═══════════════════════════════════════════════ */
[nameInput, emailInput, programInput].forEach(input => {
    if (!input) return;
    input.addEventListener('input', function () {
        const icon = this.parentElement.querySelector('.input-icon');
        const ok   = this.value.trim() !== '' && validateField(this);
        icon?.classList.toggle('show', ok);
        this.classList.toggle('valid',   ok);
        this.classList.toggle('invalid', !ok && this.value.trim() !== '');
    });
});

/* ═══════════════════════════════════════════════
   Password listener
   → updates checklist, strength bar, border colour
   → re-runs confirm check if confirm has a value
═══════════════════════════════════════════════ */
passwordInput?.addEventListener('input', function () {
    checkPasswordRequirements(this.value);
    updateStrengthBar(this.value);

    if (passwordConfirmInput.value.length > 0) {
        checkConfirmMatch();
    } else {
        // clear confirm state when password changes and confirm is empty
        passwordConfirmInput.classList.remove('valid', 'invalid');
    }
});

/* ═══════════════════════════════════════════════
   Confirm listener → real-time match feedback
═══════════════════════════════════════════════ */
passwordConfirmInput?.addEventListener('input', function () {
    checkConfirmMatch();
});

/* ═══════════════════════════════════════════════
   Step validation gate
═══════════════════════════════════════════════ */
function validateStep(step) {
    if (step === 1) return validateField(nameInput) && validateField(emailInput);
    if (step === 2) return validateField(programInput);
    if (step === 3) return validateField(passwordInput) && validateField(passwordConfirmInput);
    return false;
}

/* ═══════════════════════════════════════════════
   Step navigation
═══════════════════════════════════════════════ */
function showStep(step) {
    for (let i = 1; i <= totalSteps; i++)
        document.getElementById(`step-${i}`).classList.add('hidden');

    const el = document.getElementById(`step-${step}`);
    el.classList.remove('hidden', 'reverse');
    if (isReverse) el.classList.add('reverse');

    document.getElementById('progress-fill').style.width = `${(step / totalSteps) * 100}%`;
    document.getElementById('progress-text').textContent = `Step ${step} of ${totalSteps}`;
    document.getElementById('step-label').textContent    = stepLabels[step];

    document.getElementById('prev-btn').classList.toggle('hidden', step === 1);
    document.getElementById('next-btn').classList.toggle('hidden', step === totalSteps);
    document.getElementById('submit-btn').classList.toggle('hidden', step !== totalSteps);

    for (let i = 1; i <= totalSteps; i++) {
        const dot = document.getElementById(`step-${i}-indicator`);
        dot.classList.remove('active', 'completed');
        if (i < step) {
            dot.classList.add('completed');
            dot.innerHTML = '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>';
        } else if (i === step) {
            dot.classList.add('active');
            dot.textContent = i;
        } else {
            dot.textContent = i;
        }
        if (i < totalSteps)
            document.getElementById(`line-${i}`).classList.toggle('completed', i < step);
    }
}

function shakeStep() {
    const el = document.getElementById(`step-${currentStep}`);
    el.classList.add('shake');
    setTimeout(() => el.classList.remove('shake'), 500);
}

/* ═══════════════════════════════════════════════
   Button events
═══════════════════════════════════════════════ */
document.getElementById('next-btn').addEventListener('click', () => {
    if (!validateStep(currentStep)) { shakeStep(); return; }
    if (currentStep < totalSteps) { isReverse = false; showStep(++currentStep); }
});

document.getElementById('prev-btn').addEventListener('click', () => {
    if (currentStep > 1) { isReverse = true; showStep(--currentStep); }
});

document.getElementById('submit-btn').addEventListener('click', e => {
    if (!validateStep(currentStep)) { shakeStep(); e.preventDefault(); return; }
    document.getElementById('registration-form').submit();
});

/* ═══════════════════════════════════════════════
   Eye toggle
═══════════════════════════════════════════════ */
function togglePassword(id) {
    const input = document.getElementById(id);
    if (!input) return;
    // Find the button that triggered this (closest toggle-btn to the input)
    const btn = input.parentElement.querySelector('.toggle-btn');
    const show = input.type === 'password';
    input.type = show ? 'text' : 'password';
    // Just swap visibility of the two pre-rendered SVGs — no innerHTML touching
    btn.querySelector('.eye-open').classList.toggle('hidden', show);
    btn.querySelector('.eye-off').classList.toggle('hidden', !show);
}

/* ═══════════════════════════════════════════════
   Init
═══════════════════════════════════════════════ */
showStep(currentStep);
</script>
</body>
</html>