<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SLSU - Login</title>

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
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');

        /* Smooth entrance animation */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes scaleIn {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
                opacity: 1;
            }
            50% {
                transform: scale(1.05);
                opacity: 0.8;
            }
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-10px);
            }
        }

        @keyframes shimmer {
            0% {
                background-position: -1000px 0;
            }
            100% {
                background-position: 1000px 0;
            }
        }

        /* Background animation overlay */
        .animated-bg {
            background: linear-gradient(45deg, #0C3B2E, #6D9773, #0C3B2E);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
        }

        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Enhanced Glassmorphism Card */
        .login-card {
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3),
                        0 0 0 1px rgba(255, 255, 255, 0.1) inset;
            animation: scaleIn 0.6s ease-out;
            overflow: hidden;
        }

        /* Animated gradient button */
        .glass-button {
            position: relative;
            width: 100%;
            padding: 1rem;
            font-weight: 700;
            font-size: 1rem;
            letter-spacing: 0.5px;
            border-radius: 0.875rem;
            border: 2px solid rgba(255, 255, 255, 0.4);
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.2), rgba(255, 255, 255, 0.1));
            color: white;
            backdrop-filter: blur(10px);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            cursor: pointer;
        }

        .glass-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s;
        }

        .glass-button:hover::before {
            left: 100%;
        }

        .glass-button:hover {
            background: linear-gradient(135deg, #0C3B2E 0%, #6D9773 100%);
            border: none;
            outline: none;
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(12, 59, 46, 0.5),
                        0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .glass-button:active {
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(12, 59, 46, 0.4);
        }

        /* Enhanced input fields */
        .input-wrapper {
            position: relative;
            animation: fadeInUp 0.6s ease-out backwards;
        }

        .input-wrapper:nth-child(1) { animation-delay: 0.1s; }
        .input-wrapper:nth-child(2) { animation-delay: 0.2s; }

        .input-icon-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            width: 1.25rem;
            height: 1.25rem;
            pointer-events: none;
            z-index: 10;
            transition: all 0.3s ease;
        }

        .enhanced-input {
            width: 100%;
            padding: 1rem 1rem 1rem 3rem;
            border: 2px solid rgba(109, 151, 115, 0.3);
            border-radius: 0.875rem;
            background: rgba(255, 255, 255, 0.85);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-size: 0.95rem;
        }

        .enhanced-input:focus {
            outline: none;
            border-color: #6D9773;
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 0 0 4px rgba(109, 151, 115, 0.15),
                        0 8px 20px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .enhanced-input:focus ~ .input-icon {
            color: #0C3B2E;
            transform: translateY(-50%) scale(1.1);
        }

        /* Logo animation */
        .logo-container {
            animation: float 3s ease-in-out infinite;
        }

        .logo-ring {
            position: relative;
            display: inline-block;
            padding: 1rem;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 10px 40px rgba(12, 59, 46, 0.4),
                        0 0 0 8px rgba(255, 255, 255, 0.1);
        }

        .logo-ring::before {
            content: '';
            position: absolute;
            top: -4px;
            left: -4px;
            right: -4px;
            bottom: -4px;
            border-radius: 50%;
            background: linear-gradient(45deg, #FFBA00, #B46617, #FFBA00);
            background-size: 300% 300%;
            animation: gradientShift 3s ease infinite;
            z-index: -1;
            opacity: 0.6;
        }

        .logo-ring img {
            display: block;
            border-radius: 50%;
        }

        /* Checkbox enhancement */
        .custom-checkbox {
            appearance: none;
            width: 1.25rem;
            height: 1.25rem;
            border: 2px solid #6D9773;
            border-radius: 0.375rem;
            background: rgba(255, 255, 255, 0.8);
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }

        .custom-checkbox:checked {
            background: linear-gradient(135deg, #0C3B2E, #6D9773);
            border-color: #0C3B2E;
        }

        .custom-checkbox:checked::after {
            content: '✓';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 0.875rem;
            font-weight: bold;
        }

        /* Link hover effect */
        .animated-link {
            position: relative;
            transition: color 0.3s ease;
        }

        .animated-link::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, #B46617, #FFBA00);
            transition: width 0.3s ease;
        }

        .animated-link:hover::after {
            width: none;
        }

        /* Card sections animation */
        .left-section {
            animation: fadeIn 0.8s ease-out;
        }

        .right-section {
            animation: fadeIn 0.8s ease-out 0.2s backwards;
        }

        /* Status message */
        .status-message {
            animation: fadeInUp 0.5s ease-out;
        }

        /* Particle effect (optional decorative elements) */
        .particle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            pointer-events: none;
            animation: particleFloat 20s infinite;
        }

        @keyframes particleFloat {
            0%, 100% {
                transform: translate(0, 0) scale(1);
                opacity: 0;
            }
            10% {
                opacity: 0.6;
            }
            90% {
                opacity: 0.3;
            }
            100% {
                transform: translate(100px, -100vh) scale(0);
            }
        }

        .particle:nth-child(1) { left: 10%; animation-delay: 0s; width: 40px; height: 40px; }
        .particle:nth-child(2) { left: 30%; animation-delay: 4s; width: 60px; height: 60px; }
        .particle:nth-child(3) { left: 50%; animation-delay: 8s; width: 30px; height: 30px; }
        .particle:nth-child(4) { left: 70%; animation-delay: 12s; width: 50px; height: 50px; }
        .particle:nth-child(5) { left: 90%; animation-delay: 16s; width: 45px; height: 45px; }

        /* Footer animation */
        .footer-text {
            animation: fadeIn 1s ease-out 0.8s backwards;
        }

        /* Don't have an account to white font color*/
        .text-white-fix {
            color: white;
        }

    </style>
</head>
<body class="antialiased font-sans">

<div class="login-container relative min-h-screen flex items-center justify-center overflow-hidden">

    <!-- Background Image -->
    <img src="{{ asset('automated.png') }}" alt="Background"
         class="absolute top-0 left-0 w-full h-full object-cover z-0">

    <!-- Animated Overlay -->
    <div class="absolute inset-0 animated-bg opacity-70 z-5"></div>
    
    <!-- Decorative particles -->
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>

    <!-- Overlay -->
    <div class="absolute inset-0 bg-gradient-to-br from-primary-dark/40 to-primary-light/30 z-10"></div>

    <!-- Login Card -->
    <div class="login-card relative z-20 w-full max-w-md lg:max-w-3xl p-0 m-4">
        <div class="grid lg:grid-cols-2">

            <!-- Left Side -->
            <div class="left-section p-8 md:p-10 text-center bg-primary-dark/70 text-white flex flex-col justify-center items-center">
                <div class="logo-container">
                    <div class="logo-ring">
                        <img src="{{ asset('slsu-logo.png') }}" alt="SLSU Logo" class="w-20 h-20 object-contain rounded-full">
                    </div>
                </div>

                <h1 class="text-3xl md:text-4xl font-extrabold mb-3 mt-6 bg-gradient-to-r from-white to-gray-200 bg-clip-text text-transparent">
                    Welcome Back!
                </h1>
                <p class="text-sm font-light opacity-90 max-w-xs leading-relaxed">
                    Automated Scheduling Portal for South Luzon State University Tiaong Campus. Please sign in to continue.
                </p>
            </div>

            <!-- Right Side (Form) -->
            <div class="right-section p-8 md:p-10 space-y-6 bg-white/10 backdrop-blur-lg">
                <h2 class="text-2xl font-bold text-primary-dark mb-6">Sign In to Your Account</h2>

                <!-- Status Message (if exists) -->
                @if (session('status'))
                    <div class="status-message mb-4 p-4 bg-teal-50 border-l-4 border-teal-500 text-teal-800 rounded-lg text-sm">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="status-message mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-800 rounded-lg text-sm">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="flex flex-col gap-5">
                    @csrf

                    <!-- Email -->
                    <div class="input-wrapper">
                        <label for="email" class="block text-sm font-semibold text-primary-dark mb-2">
                            Email / Username
                        </label>
                        <div class="input-icon-wrapper">
                            <svg class="input-icon text-primary-light" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <input id="email" 
                                   class="enhanced-input" 
                                   type="email" 
                                   name="email" 
                                   value="{{ old('email') }}"
                                   placeholder="Enter your email or username" 
                                   required 
                                   autofocus>
                        </div>
                        <!-- @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        -->
                    </div>

                    <!-- Password -->
                    <div class="input-wrapper">
                        <label for="password" class="block text-sm font-semibold text-primary-dark mb-2">
                            Password
                        </label>

                        <div class="input-icon-wrapper relative">

                            <!-- Left Lock Icon -->
                            <svg class="input-icon text-primary-light" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>

                            <!-- Password Input -->
                            <input id="password"
                                class="enhanced-input pr-12"
                                type="password"
                                name="password"
                                placeholder="Enter your password"
                                required>

                            <!-- View / Hide Password Toggle Button -->
                            <button type="button"
                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-primary-dark/70 hover:text-primary-dark"
                                    onclick="toggleLoginPassword(this)">
                                
                                <!-- Eye Icon (default) -->
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7
                                        -1.274 4.057-5.065 7-9.542 7 -4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                        </div>

                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Remember + Forgot -->
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3" style="animation: fadeInUp 0.6s ease-out 0.3s backwards;">
                        <label class="flex items-center cursor-pointer group">
                            <input type="checkbox" name="remember" class="custom-checkbox">
                            <span class="ml-2 text-sm text-gray-800 font-semibold group-hover:text-primary-dark transition-colors">
                                Remember me
                            </span>
                        </label>

                        @if (Route::has('password.request'))
                            <a class="animated-link text-sm font-semibold text-accent-brown hover:text-accent-brown/80 transition-colors" 
                               href="{{ route('password.request') }}">
                                Forgot password?
                            </a>
                        @endif
                    </div>

                    <!-- Glass Button -->
                    <div style="animation: fadeInUp 0.6s ease-out 0.4s backwards;">
                        <button type="submit" class="glass-button">
                            Sign In
                        </button>
                    </div>

                    <!-- Don't have account -->
                    <div class="text-center" style="animation: fadeInUp 0.6s ease-out 0.5s backwards;">
                        <p class="text-sm text-white-fix">
                            Don't have an account? 
                            <a href="{{ route('register') }}" class="animated-link font-semibold text-[#FFBA00] transition-colors ml-1">
                                Register
                            </a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <p class="footer-text absolute bottom-4 text-center text-sm text-white/90 z-20 font-medium tracking-wide">
        © <span id="year"></span> South Luzon State University - Tiaong Campus
    </p>
</div>

<script>
    // Set current year
    document.getElementById('year').textContent = new Date().getFullYear();

    // Add smooth focus transitions
    const inputs = document.querySelectorAll('.enhanced-input');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.style.transform = 'scale(1.01)';
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.style.transform = 'scale(1)';
        });
    });

    // Button ripple effect on click
    const button = document.querySelector('.glass-button');
    button.addEventListener('click', function(e) {
        const ripple = document.createElement('span');
        const rect = this.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = e.clientX - rect.left - size / 2;
        const y = e.clientY - rect.top - size / 2;
        
        ripple.style.cssText = `
            position: absolute;
            width: ${size}px;
            height: ${size}px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.5);
            top: ${y}px;
            left: ${x}px;
            pointer-events: none;
            transform: scale(0);
            animation: rippleEffect 0.6s ease-out;
        `;
        
        this.appendChild(ripple);
        
        setTimeout(() => ripple.remove(), 600);
    });

    // Add ripple animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes rippleEffect {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);

    function toggleLoginPassword(btn) {
    const input = document.getElementById('password');
    const isHidden = input.type === "password";

    input.type = isHidden ? "text" : "password";

    btn.innerHTML = isHidden
        ? `<!-- Eye Off --> 
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7
                    .563 -1.79 1.63-3.35 3.042-4.542M6.18 6.18A9.956 9.956 0 0112 5c4.477 0 
                    8.268 2.943 9.542 7-.46 1.466-1.26 2.788-2.304 3.85M3 3l18 18"/>
            </svg>`
        : `<!-- Eye On -->
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7
                    -1.274 4.057-5.065 7-9.542 7 -4.477 0-8.268-2.943-9.542-7z"/>
            </svg>`;
    }
</script>

</body>
</html>

