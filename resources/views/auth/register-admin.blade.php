<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SLSU - Admin Registration</title>
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
            display: flex;
            align-items: center;
            justify-content: center;
            height: 58px;
            padding: 0 1rem;
            font-weight: 600;
            border-radius: 0.75rem;
            
            /* full gradient background - changed #1 */ 
            background: rgba(255, 255, 255, 0.15);
            
            /* remove white border + glass effect - changed #2 */
            border: 1.5px solid rgba(255, 255, 255, 0.35);
            backdrop-filter: blur(12px);

            color: white;
            transition: all 0.3s ease;
            cursor: pointer;

            position: relative;
            overflow: hidden;
        }


        /* Shimmer effect */
        .glass-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.6s ease;
        }
        
        .glass-button:hover::before {
            left: 100%;
        }

        .glass-button span {
            line-height: 1.1;
            text-align: center;
        }

        .glass-button:hover:not(:disabled) {
            background: linear-gradient(90deg, #0C3B2E 0%, #6D9773 100%);
            border: none;
            backdrop-filter: none;

            opacity: 1;
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(12, 59, 46, 0.4);
        }

        .glass-button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .form-input {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid rgba(109, 151, 115, 0.7);
            border-radius: 1rem;
            background-color: rgba(255, 255, 255, 0.7);
            transition: all 0.3s ease;
            color: #ffffffff;
        }

        .form-input::placeholder {
            color: rgba(12, 59, 46, 0.6);  /* soft green-gray */
        }

        .form-input:focus {
            border-color: #6D9773;
            outline: none;
            box-shadow: 0 0 0 3px rgba(109, 151, 115, 0.2);
            transform: scale(1.01);
        }

        .form-input.filled {
            border-color:  #FFBA00;
            background-color: rgba(78, 255, 165, 0.08);
            box-shadow: 0 0 12px #FFBA00;
        }

        .form-input.error {
            border-color: #ef4444;
            box-shadow: none;
            background-color: rgba(255, 0, 0, 0.08);
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
            background: linear-gradient(90deg, #FFBA00 0%, #B46617 100%);
            border-radius: 10px;
            transition: width 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 0 10px rgba(255, 186, 0, 0.5);
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
            background: linear-gradient(135deg, #FFBA00 0%, #B46617 100%);
            border-color: #FFBA00;
            box-shadow: 0 0 20px rgba(255, 186, 0, 0.6);
            transform: scale(1.1);
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
            top: 68%;
            transform: translateY(-50%);
            opacity: 0;
            transition: all 0.3s ease;
        }

        #name + .input-icon,
        #email + .input-icon,
        #password + .input-icon,
        #password_confirmation + .input-icon {
            top: 68%;
        }

        #faculty_id + .input-icon {
            top: 52%;
        }

        .input-icon svg {
            stroke: #4EFFA5 !important;  /* bright neon green */
            filter: drop-shadow(0 0 6px rgba(78, 255, 165, 0.9));
            stroke-width: 2.8 !important;
            opacity: 1 !important;
        }
        .input-icon.show {
            opacity: 1;
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
                    <h1 class="text-3xl font-extrabold mb-2">Admin Registration</h1>
                    <p class="text-sm font-light opacity-80 max-w-xs">
                        Create an admin account to manage programs, faculty, and schedules.
                    </p>
                    <div class="mt-6 flex items-center gap-2 text-accent-yellow">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        <span class="text-sm font-semibold">Administrator Access</span>
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
                <div class="p-8 md:p-10 bg-white/10 backdrop-blur-lg rounded-r-2xl relative overflow-hidden min-h-[600px] flex flex-col justify-between">

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
                                </div>

                                <div class="input-wrapper">
                                    <label for="email" class="block text-sm font-medium text-primary-dark mb-2">Email Address</label>
                                    <input id="email" class="form-input" type="email" name="email" value="{{ old('email') }}" placeholder="your.email@slsu.edu.ph" required>
                                    <div class="input-icon">
                                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 2: Program Details -->
                        <div class="step-content hidden" id="step-2">
                            <h2 class="text-xl font-bold text-primary-dark mb-6">Program Details</h2>
                            
                            <div class="space-y-5">
                                <div class="input-wrapper">
                                    <label for="program" class="block text-sm font-medium text-primary-dark mb-2">Program</label>
                                    <input id="program" class="form-input" type="text" name="program" value="{{ old('program') }}" placeholder="e.g., BSIT, BSCS, BSBA" required>
                                    <div class="input-icon">
                                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                    <p class="text-xs text-gray-600 mt-2">Enter the program code you will manage</p>
                                </div>
                            </div>
                        </div>

                        <!-- Step 3: Security -->
                        <div class="step-content hidden" id="step-3">
                            <h2 class="text-xl font-bold text-primary-dark mb-6">Security</h2>
                            
                            <div class="space-y-5">
                                <div class="input-wrapper">
                                    <label for="password" class="block text-sm font-medium text-primary-dark mb-2">Password</label>

                                    <div class="relative">
                                        <input id="password" 
                                            type="password" 
                                            name="password" 
                                            placeholder="Minimum 8 characters"
                                            class="form-input pr-12"
                                            required>

                                        <!-- Toggle Icon -->
                                        <button type="button" 
                                                class="absolute right-5 top-1/2 -translate-y-1/2 flex items-center p-2 cursor-pointer text-primary-dark/80 hover:text-primary-dark"
                                                onclick="togglePassword('password', this)">
                                            <!-- Eye Icon -->
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7
                                                    -1.274 4.057-5.065 7-9.542 7 -4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </button>
                                    </div>                                    
                                    
                                    <div class="input-icon">
                                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                </div>

                                <div class="input-wrapper">
                                    <label for="password_confirmation" class="block text-sm font-medium text-primary-dark mb-2">Confirm Password</label>
                            
                                    <div class="relative">
                                        <input id="password_confirmation" 
                                            type="password" 
                                            name="password_confirmation" 
                                            placeholder="Re-enter your password"
                                            class="form-input pr-12"
                                            required>

                                        <!-- Toggle Icon -->
                                        <button type="button" 
                                                class="absolute right-5 top-1/2 -translate-y-1/2 flex items-center p-2 cursor-pointer text-primary-dark/80 hover:text-primary-dark"
                                                onclick="togglePassword('password_confirmation', this)">
                                            <!-- Eye Icon -->
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7
                                                    -1.274 4.057-5.065 7-9.542 7 -4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </button>
                                    </div>                                    
                                    
                                    <div class="input-icon">
                                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                </div>

                                <div id="password-strength" class="hidden">
                                    <div class="flex gap-1 mb-2">
                                        <div class="h-1 flex-1 rounded bg-gray-300" id="strength-1"></div>
                                        <div class="h-1 flex-1 rounded bg-gray-300" id="strength-2"></div>
                                        <div class="h-1 flex-1 rounded bg-gray-300" id="strength-3"></div>
                                        <div class="h-1 flex-1 rounded bg-gray-300" id="strength-4"></div>
                                    </div>
                                    <p class="text-xs" id="strength-text">Password strength: Weak</p>
                                </div>
                            </div>
                        </div>

                        <!-- Navigation Buttons -->
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
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Complete Registration
                                </span>
                            </button>
                        </div>

                        <!-- Links -->
                        <div class="mt-12 space-y-2">
                            <p class="text-sm text-white text-center">
                                Already have an account? <a href="{{ route('login') }}" class="text-[#FFBA00] font-medium transition">Sign In</a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
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
            2: 'Program Details',
            3: 'Security'
        };

        // Get all form inputs
        const nameInput = document.getElementById('name');
        const emailInput = document.getElementById('email');
        const programInput = document.getElementById('program');
        const passwordInput = document.getElementById('password');
        const passwordConfirmInput = document.getElementById('password_confirmation');

        // Add input validation and styling
        [nameInput, emailInput, programInput, passwordInput, passwordConfirmInput].forEach(input => {
            input.addEventListener('input', function() {
                const icon = this.parentElement.querySelector('.input-icon');
                if (this.value.trim() !== '') {
                    this.classList.add('filled');
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
        });

        // Password strength indicator
        passwordInput.addEventListener('input', function() {
            const strength = calculatePasswordStrength(this.value);
            updatePasswordStrength(strength);
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
            const strengthText = document.getElementById('strength-text');
            const bars = [1, 2, 3, 4].map(i => document.getElementById(`strength-${i}`));
            
            strengthDiv.classList.remove('hidden');
            
            const colors = ['bg-red-500', 'bg-orange-500', 'bg-yellow-500', 'bg-green-500'];
            const texts = ['Weak', 'Fair', 'Good', 'Strong'];
            
            bars.forEach((bar, index) => {
                bar.className = 'h-1 flex-1 rounded bg-gray-300';
                if (index < strength) {
                    bar.classList.add(colors[strength - 1]);
                }
            });
            
            strengthText.textContent = `Password strength: ${texts[strength - 1] || 'Weak'}`;
            strengthText.className = `text-xs ${strength > 2 ? 'text-green-600' : strength > 1 ? 'text-yellow-600' : 'text-red-600'}`;
        }

        function validateField(input) {
            if (input.id === 'email') {
                return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(input.value);
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
            } else if (step === 2) {
                isValid = validateField(programInput);
            } else if (step === 3) {
                isValid = validateField(passwordInput) && validateField(passwordConfirmInput);
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

        document.getElementById('submit-btn').addEventListener('click', function() {
            if (!validateStep(currentStep)) {
                const currentStepEl = document.getElementById(`step-${currentStep}`);
                currentStepEl.classList.add('shake');
                setTimeout(() => currentStepEl.classList.remove('shake'), 500);
                return;
            }
            document.getElementById('registration-form').submit();
        });

        // Initialize
        showStep(currentStep);

    function togglePassword(id, btn) {
    const input = document.getElementById(id);
    const isPassword = input.type === "password";

    input.type = isPassword ? "text" : "password";

    // Swap icon when toggling
    btn.innerHTML = isPassword
        ? `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7
                    .563 -1.79 1.63-3.35 3.042-4.542M6.18 6.18A9.956 9.956 0 0112 5c4.477 0 8.268 2.943 
                    9.542 7-.46 1.466-1.26 2.788-2.304 3.85M3 3l18 18"/>
           </svg>`
        : `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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