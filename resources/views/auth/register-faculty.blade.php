<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SLSU - Faculty Registration</title>
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
                    }
                }
            }
        }
    </script>
    <style>
        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        @keyframes shimmer {
            0% { background-position: -1000px 0; }
            100% { background-position: 1000px 0; }
        }

        .login-card {
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(12px);
            border-radius: 20px;
            box-shadow: 0 8px 40px rgba(0, 0, 0, 0.25);
            animation: fadeIn 0.6s ease-out;
        }

        .step-content {
            animation: slideInRight 0.4s ease-out;
        }

        .step-content.reverse {
            animation: slideInLeft 0.4s ease-out;
        }

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
            position: relative;
            overflow: hidden;
        }

        .glass-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .glass-button:hover::before {
            left: 100%;
        }

        .glass-button:hover:not(:disabled) {
            background: linear-gradient(90deg, #0C3B2E 0%, #6D9773 100%);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(12, 59, 46, 0.4);
        }

        .glass-button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .secondary-button {
            background: rgba(109, 151, 115, 0.2);
            border: 1px solid rgba(109, 151, 115, 0.4);
        }

        .secondary-button:hover:not(:disabled) {
            background: rgba(109, 151, 115, 0.3);
            transform: translateY(-2px);
        }

        .form-input {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid rgba(109, 151, 115, 0.3);
            border-radius: 1rem;
            background-color: rgba(255, 255, 255, 0.7);
            transition: all 0.3s ease;
        }

        .form-input:focus {
            border-color: #6D9773;
            outline: none;
            box-shadow: 0 0 0 3px rgba(109, 151, 115, 0.2);
            transform: scale(1.01);
        }

        .form-input.filled {
            border-color: #6D9773;
            background-color: rgba(109, 151, 115, 0.1);
        }

        .form-input.error {
            border-color: #ef4444;
            background-color: rgba(239, 68, 68, 0.1);
        }

        .progress-bar {
            height: 6px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            overflow: hidden;
            position: relative;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #6D9773 0%, #FFBA00 100%);
            border-radius: 10px;
            transition: width 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 0 10px rgba(109, 151, 115, 0.5);
            position: relative;
            overflow: hidden;
        }

        .progress-fill::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            bottom: 0;
            right: 0;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            animation: shimmer 2s infinite;
        }

        .step-indicator {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.3);
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
        }

        .step-indicator.active {
            background: linear-gradient(135deg, #6D9773 0%, #FFBA00 100%);
            border-color: #6D9773;
            box-shadow: 0 0 20px rgba(109, 151, 115, 0.6);
            transform: scale(1.1);
            animation: pulse 2s infinite;
        }

        .step-indicator.completed {
            background: #6D9773;
            border-color: #6D9773;
        }

        .step-line {
            height: 2px;
            background: rgba(255, 255, 255, 0.2);
            flex: 1;
            margin: 0 8px;
            position: relative;
            overflow: hidden;
        }

        .step-line.completed::after {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 100%;
            background: #6D9773;
            animation: fillLine 0.5s ease-out;
        }

        @keyframes fillLine {
            from { width: 0; }
            to { width: 100%; }
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            opacity: 0;
            transition: all 0.3s ease;
        }

        .input-icon.show {
            opacity: 1;
            animation: bounceIn 0.5s;
        }

        @keyframes bounceIn {
            0% { transform: translateY(-50%) scale(0); }
            50% { transform: translateY(-50%) scale(1.2); }
            100% { transform: translateY(-50%) scale(1); }
        }

        .shake {
            animation: shake 0.5s;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        .floating {
            animation: floating 3s ease-in-out infinite;
        }

        @keyframes floating {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .tooltip {
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%) translateY(-8px);
            background: rgba(12, 59, 46, 0.95);
            color: white;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.75rem;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s;
            z-index: 10;
        }

        .tooltip::after {
            content: '';
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            border: 4px solid transparent;
            border-top-color: rgba(12, 59, 46, 0.95);
        }

        .input-wrapper:hover .tooltip {
            opacity: 1;
        }
    </style>
</head>
<body class="antialiased font-sans">
    <div class="relative min-h-screen flex items-center justify-center overflow-hidden">
        <img src="{{ asset('automated.png') }}" alt="Background" class="absolute inset-0 w-full h-full object-cover z-0">
        <div class="absolute inset-0 bg-gradient-to-br from-primary-dark/70 to-primary-light/60 z-10"></div>

        <div class="login-card relative z-20 w-full max-w-md lg:max-w-4xl">
            <div class="grid lg:grid-cols-2">
                <!-- Left Side -->
                <div class="p-8 md:p-10 text-center bg-primary-dark/70 text-white flex flex-col justify-center items-center rounded-l-2xl">
                    <div class="inline-block p-4 mb-4 rounded-full bg-gradient-to-br from-primary-light to-primary-dark shadow-lg floating">
                        <img src="{{ asset('slsu-logo.png') }}" alt="SLSU Logo" class="w-16 h-16 object-contain rounded-full">
                    </div>
                    <h1 class="text-3xl font-extrabold mb-2">Faculty Registration</h1>
                    <p class="text-sm font-light opacity-80 max-w-xs">
                        Create a faculty account to access your schedules and manage your courses.
                    </p>
                    <div class="mt-6 flex items-center gap-2 text-primary-light">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                        <span class="text-sm font-semibold">Faculty Access</span>
                    </div>

                    <!-- Progress Steps -->
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

                    <!-- Overall Progress Bar -->
                    <div class="w-full max-w-sm mt-4">
                        <div class="progress-bar">
                            <div class="progress-fill" id="progress-fill" style="width: 33.33%"></div>
                        </div>
                        <p class="text-xs mt-2 opacity-70" id="progress-text">Step 1 of 3</p>
                    </div>
                </div>

                <!-- Right Side (Form) -->
                <div class="p-8 md:p-10 bg-white/10 backdrop-blur-lg rounded-r-2xl relative overflow-hidden">
                    @if ($errors->any())
                        <div class="mb-4 p-3 bg-red-100 border border-red-400 text-red-800 rounded-lg text-sm">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('register.faculty') }}" id="registration-form">
                        @csrf

                        <!-- Step 1: Personal Information -->
                        <div class="step-content" id="step-1">
                            <h2 class="text-xl font-bold text-primary-dark mb-6">Personal Information</h2>
                            
                            <div class="space-y-5">
                                <div class="input-wrapper">
                                    <label for="name" class="block text-sm font-medium text-primary-dark mb-2">Full Name</label>
                                    <input id="name" class="form-input" type="text" name="name" value="{{ old('name') }}" placeholder="Enter your full name" required>
                                    <div class="input-icon">
                                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                    <div class="tooltip">Enter your complete legal name</div>
                                </div>

                                <div class="input-wrapper">
                                    <label for="email" class="block text-sm font-medium text-primary-dark mb-2">Email Address</label>
                                    <input id="email" class="form-input" type="email" name="email" value="{{ old('email') }}" placeholder="faculty.name@slsu.edu.ph" required>
                                    <div class="input-icon">
                                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                    <div class="tooltip">Use your official SLSU email</div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 2: Faculty Details -->
                        <div class="step-content hidden" id="step-2">
                            <h2 class="text-xl font-bold text-primary-dark mb-6">Faculty Details</h2>
                            
                            <div class="space-y-5">
                                <div class="input-wrapper">
                                    <label for="faculty_id" class="block text-sm font-medium text-primary-dark mb-2">Faculty ID</label>
                                    <input id="faculty_id" class="form-input" type="text" name="faculty_id" value="{{ old('faculty_id') }}" placeholder="e.g., FAC-2024-001" required>
                                    <div class="input-icon">
                                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                    <div class="tooltip">Your unique faculty identification number</div>
                                    <p class="text-xs text-gray-600 mt-2">Enter your assigned faculty identification number</p>
                                </div>

                                <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                    <div class="flex gap-2">
                                        <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <div class="text-sm text-blue-800">
                                            <p class="font-semibold mb-1">Faculty ID Format</p>
                                            <p class="text-xs">Your Faculty ID can be found on your employee badge or obtained from the HR department.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 3: Security -->
                        <div class="step-content hidden" id="step-3">
                            <h2 class="text-xl font-bold text-primary-dark mb-6">Security</h2>
                            
                            <div class="space-y-5">
                                <div class="input-wrapper">
                                    <label for="password" class="block text-sm font-medium text-primary-dark mb-2">Password</label>
                                    <input id="password" class="form-input" type="password" name="password" placeholder="Minimum 8 characters" required>
                                    <div class="input-icon">
                                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                    <div class="tooltip">Use a strong, unique password</div>
                                </div>

                                <div class="input-wrapper">
                                    <label for="password_confirmation" class="block text-sm font-medium text-primary-dark mb-2">Confirm Password</label>
                                    <input id="password_confirmation" class="form-input" type="password" name="password_confirmation" placeholder="Re-enter your password" required>
                                    <div class="input-icon">
                                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                    <div class="tooltip">Passwords must match</div>
                                </div>

                                <div id="password-strength" class="hidden">
                                    <div class="flex gap-1 mb-2">
                                        <div class="h-1.5 flex-1 rounded bg-gray-300 transition-all duration-300" id="strength-1"></div>
                                        <div class="h-1.5 flex-1 rounded bg-gray-300 transition-all duration-300" id="strength-2"></div>
                                        <div class="h-1.5 flex-1 rounded bg-gray-300 transition-all duration-300" id="strength-3"></div>
                                        <div class="h-1.5 flex-1 rounded bg-gray-300 transition-all duration-300" id="strength-4"></div>
                                    </div>
                                    <p class="text-xs flex items-center gap-2" id="strength-text">
                                        <span class="inline-block w-2 h-2 rounded-full" id="strength-dot"></span>
                                        Password strength: <span id="strength-label">Weak</span>
                                    </p>
                                </div>

                                <div class="p-3 bg-green-50 border border-green-200 rounded-lg">
                                    <p class="text-xs text-green-800">
                                        <span class="font-semibold">Security Tips:</span> Use a mix of uppercase, lowercase, numbers, and special characters for a stronger password.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Navigation Buttons -->
                        <div class="flex gap-3 mt-8">
                            <button type="button" id="prev-btn" class="glass-button secondary-button hidden">
                                <span class="flex items-center justify-center gap-2 relative z-10">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                    </svg>
                                    Previous
                                </span>
                            </button>
                            <button type="button" id="next-btn" class="glass-button">
                                <span class="flex items-center justify-center gap-2 relative z-10">
                                    Next
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </span>
                            </button>
                            <button type="submit" id="submit-btn" class="glass-button hidden">
                                <span class="flex items-center justify-center gap-2 relative z-10">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Complete Registration
                                </span>
                            </button>
                        </div>

                        <!-- Links -->
                        <div class="mt-6 space-y-2">
                            <p class="text-sm text-gray-800 text-center">
                                Already have an account? <a href="{{ route('login') }}" class="text-accent-brown font-medium hover:text-accent-brown/80 transition">Sign In</a>
                            </p>
                            <p class="text-sm text-gray-800 text-center">
                                <a href="{{ route('welcome') }}" class="text-primary-light font-medium hover:text-primary-light/80 transition">← Back to role selection</a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <p class="absolute bottom-4 text-center text-xs text-white/80 z-20">
            © {{ date('Y') }} South Luzon State University - Tiaong Campus
        </p>
    </div>

    <script>
        let currentStep = 1;
        const totalSteps = 3;
        let isReverse = false;

        const stepLabels = {
            1: 'Personal Information',
            2: 'Faculty Details',
            3: 'Security'
        };

        // Get all form inputs
        const nameInput = document.getElementById('name');
        const emailInput = document.getElementById('email');
        const facultyIdInput = document.getElementById('faculty_id');
        const passwordInput = document.getElementById('password');
        const passwordConfirmInput = document.getElementById('password_confirmation');

        // Add input validation and styling
        [nameInput, emailInput, facultyIdInput, passwordInput, passwordConfirmInput].forEach(input => {
            input.addEventListener('input', function() {
                const icon = this.parentElement.querySelector('.input-icon');
                if (this.value.trim() !== '') {
                    this.classList.add('filled');
                    this.classList.remove('error');
                    if (validateField(this)) {
                        icon?.classList.add('show');
                    } else {
                        icon?.classList.remove('show');
                    }
                } else {
                    this.classList.remove('filled');
                    icon?.classList.remove('show');
                }
            });

            input.addEventListener('blur', function() {
                if (this.value.trim() !== '' && !validateField(this)) {
                    this.classList.add('error');
                }
            });
        });

        // Password strength indicator
        passwordInput.addEventListener('input', function() {
            const strength = calculatePasswordStrength(this.value);
            updatePasswordStrength(strength);
        });

        // Password confirmation matching
        passwordConfirmInput.addEventListener('input', function() {
            const icon = this.parentElement.querySelector('.input-icon');
            if (this.value === passwordInput.value && this.value.length >= 8) {
                icon?.classList.add('show');
                this.classList.remove('error');
            } else {
                icon?.classList.remove('show');
                if (this.value.length > 0) {
                    this.classList.add('error');
                }
            }
        });

        function calculatePasswordStrength(password) {
            let strength = 0;
            if (password.length >= 8) strength++;
            if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;
            return strength;
        }

        function updatePasswordStrength(strength) {
            const strengthDiv = document.getElementById('password-strength');
            const strengthLabel = document.getElementById('strength-label');
            const strengthDot = document.getElementById('strength-dot');
            const bars = [1, 2, 3, 4].map(i => document.getElementById(`strength-${i}`));
            
            strengthDiv.classList.remove('hidden');
            
            const colors = ['bg-red-500', 'bg-orange-500', 'bg-yellow-500', 'bg-green-500'];
            const dotColors = ['bg-red-500', 'bg-orange-500', 'bg-yellow-500', 'bg-green-500'];
            const textColors = ['text-red-600', 'text-orange-600', 'text-yellow-600', 'text-green-600'];
            const labels = ['Weak', 'Fair', 'Good', 'Strong'];
            
            bars.forEach((bar, index) => {
                bar.className = 'h-1.5 flex-1 rounded bg-gray-300 transition-all duration-300';
                if (index < strength) {
                    bar.classList.add(colors[strength - 1]);
                }
            });
            
            strengthLabel.textContent = labels[strength - 1] || 'Weak';
            strengthDot.className = `inline-block w-2 h-2 rounded-full ${dotColors[strength - 1] || 'bg-red-500'}`;
            
            const strengthText = document.getElementById('strength-text');
            strengthText.className = `text-xs flex items-center gap-2 ${textColors[strength - 1] || 'text-red-600'}`;
        }

        function validateField(input) {
            if (input.id === 'email') {
                return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(input.value);
            }
            if (input.id === 'faculty_id') {
                return input.value.trim().length >= 3;
            }
            if (input.id === 'password') {
                return input.value.length >= 8;
            }
            if (input.id === 'password_confirmation') {
                return input.value === passwordInput.value && input.value.length >= 8;
            }
            return input.value.trim() !== '';
        }

        function validateStep(step) {
            let isValid = true;
            
            if (step === 1) {
                isValid = validateField(nameInput) && validateField(emailInput);
                if (!isValid) {
                    if (!validateField(nameInput)) nameInput.classList.add('error');
                    if (!validateField(emailInput)) emailInput.classList.add('error');
                }
            } else if (step === 2) {
                isValid = validateField(facultyIdInput);
                if (!isValid) {
                    facultyIdInput.classList.add('error');
                }
            } else if (step === 3) {
                isValid = validateField(passwordInput) && validateField(passwordConfirmInput);
                if (!isValid) {
                    if (!validateField(passwordInput)) passwordInput.classList.add('error');
                    if (!validateField(passwordConfirmInput)) passwordConfirmInput.classList.add('error');
                }
            }
            
            return isValid;
        }

        function showStep(step) {
            // Hide all steps
            for (let i = 1; i <= totalSteps; i++) {
                const stepEl = document.getElementById(`step-${i}`);
                stepEl.classList.add('hidden');
            }
            
            // Show current step
            const currentStepEl = document.getElementById(`step-${step}`);
            currentStepEl.classList.remove('hidden');
            currentStepEl.classList.remove('reverse');
            if (isReverse) {
                currentStepEl.classList.add('reverse');
            }
            
            // Update progress
            updateProgress(step);
            
            // Update buttons
            updateButtons(step);
            
            // Update step indicators
            updateStepIndicators(step);
        }

        function updateProgress(step) {
            const progress = (step / totalSteps) * 100;
            document.getElementById('progress-fill').style.width = `${progress}%`;
            document.getElementById('progress-text').textContent = `Step ${step} of ${totalSteps}`;
            document.getElementById('step-label').textContent = stepLabels[step];
        }

        function updateButtons(step) {
            const prevBtn = document.getElementById('prev-btn');
            const nextBtn = document.getElementById('next-btn');
            const submitBtn = document.getElementById('submit-btn');
            
            if (step === 1) {
                prevBtn.classList.add('hidden');
                nextBtn.classList.remove('hidden');
                submitBtn.classList.add('hidden');
            } else if (step === totalSteps) {
                prevBtn.classList.remove('hidden');
                nextBtn.classList.add('hidden');
                submitBtn.classList.remove('hidden');
            } else {
                prevBtn.classList.remove('hidden');
                nextBtn.classList.remove('hidden');
                submitBtn.classList.add('hidden');
            }
        }

        function updateStepIndicators(step) {
            for (let i = 1; i <= totalSteps; i++) {
                const indicator = document.getElementById(`step-${i}-indicator`);
                indicator.classList.remove('active', 'completed');
                
                if (i < step) {
                    indicator.classList.add('completed');
                    indicator.innerHTML = '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>';
                } else if (i === step) {
                    indicator.classList.add('active');
                    indicator.textContent = i;
                } else {
                    indicator.textContent = i;
                }
                
                // Update lines
                if (i < totalSteps) {
                    const line = document.getElementById(`line-${i}`);
                    if (i < step) {
                        line.classList.add('completed');
                    } else {
                        line.classList.remove('completed');
                    }
                }
            }
        }

        document.getElementById('next-btn').addEventListener('click', function() {
            if (!validateStep(currentStep)) {
                const currentStepEl = document.getElementById(`step-${currentStep}`);
                currentStepEl.classList.add('shake');
                setTimeout(() => currentStepEl.classList.remove('shake'), 500);
                return;
            }
            
            if (currentStep < totalSteps) {
                isReverse = false;
                currentStep++;
                showStep(currentStep);
            }
        });

        document.getElementById('prev-btn').addEventListener('click', function() {
            if (currentStep > 1) {
                isReverse = true;
                currentStep--;
                showStep(currentStep);
            }
        });

        document.getElementById('submit-btn').addEventListener('click', function(e) {
            if (!validateStep(currentStep)) {
                e.preventDefault();
                const currentStepEl = document.getElementById(`step-${currentStep}`);
                currentStepEl.classList.add('shake');
                setTimeout(() => currentStepEl.classList.remove('shake'), 500);
                return;
            }
        });

        // Initialize
        showStep(currentStep);
         </script>
</body>
</html>