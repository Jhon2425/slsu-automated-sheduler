<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SLSU Automated Scheduler - Tiaong Campus</title>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            overflow-x: hidden;
        }

        .hero-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background-image: url('{{ asset("images/university-bg.jpg") }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            position: relative;
        }

        .hero-container::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(12, 59, 46, 0.88), rgba(109, 151, 115, 0.85));
            z-index: 1;
        }

        /* Animated decorative elements */
        .decoration {
            position: absolute;
            border-radius: 50%;
            opacity: 0.15;
            animation: float 6s ease-in-out infinite;
        }

        .decoration-1 {
            width: 300px;
            height: 300px;
            background-color: #FFBA00;
            top: 10%;
            right: 10%;
            animation-delay: 0s;
        }

        .decoration-2 {
            width: 200px;
            height: 200px;
            background-color: #B46617;
            bottom: 15%;
            left: 8%;
            animation-delay: 2s;
        }

        .decoration-3 {
            width: 150px;
            height: 150px;
            background-color: #6D9773;
            top: 60%;
            right: 15%;
            animation-delay: 4s;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-20px);
            }
        }

        .content-wrapper {
            position: relative;
            z-index: 10;
            text-align: center;
            max-width: 900px;
            width: 100%;
        }

        .logo-section {
            margin-bottom: 2rem;
            animation: fadeInDown 1s ease-out;
        }

        .logo-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 8rem;
            height: 8rem;
            background: linear-gradient(135deg, rgba(109, 151, 115, 0.95), rgba(12, 59, 46, 0.95));
            border-radius: 50%;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            margin-bottom: 1.5rem;
            border: 4px solid rgba(255, 186, 0, 0.3);
            backdrop-filter: blur(10px);
        }

        .logo-icon {
            width: 5rem;
            height: 5rem;
            object-fit: contain;
            border-radius: 50%;
        }

        .university-name {
            font-size: 1.25rem;
            color: white;
            font-weight: 600;
            margin-bottom: 0.5rem;
            letter-spacing: 0.5px;
        }

        .campus-name {
            font-size: 1rem;
            color: rgba(255, 255, 255, 0.9);
            font-weight: 400;
        }

        .main-heading {
            font-size: 3.5rem;
            font-weight: 800;
            color: white;
            margin-bottom: 1.5rem;
            line-height: 1.2;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            animation: fadeInUp 1s ease-out 0.2s both;
        }

        .tagline {
            font-size: 1.5rem;
            color: rgba(255, 255, 255, 0.95);
            margin-bottom: 1rem;
            font-weight: 500;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            animation: fadeInUp 1s ease-out 0.4s both;
        }

        .subtitle {
            font-size: 1.125rem;
            color: rgba(255, 255, 255, 0.85);
            margin-bottom: 3rem;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.6;
            animation: fadeInUp 1s ease-out 0.6s both;
        }

        .button-group {
            display: flex;
            gap: 1.5rem;
            justify-content: center;
            flex-wrap: wrap;
            animation: fadeInUp 1s ease-out 0.8s both;
        }

        .btn {
            padding: 1rem 2.5rem;
            font-size: 1.125rem;
            font-weight: 600;
            border-radius: 0.75rem;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .btn-primary {
            background: linear-gradient(135deg, #FFBA00, #B46617);
            color: #0C3B2E;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(255, 186, 0, 0.4);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(10px);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(255, 255, 255, 0.2);
        }

        .features-section {
            margin-top: 4rem;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            animation: fadeInUp 1s ease-out 1s both;
        }

        .feature-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 1.5rem;
            border-radius: 1rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }

        .feature-card:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-5px);
        }

        .feature-icon {
            width: 3rem;
            height: 3rem;
            margin: 0 auto 1rem;
            color: #FFBA00;
        }

        .feature-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: white;
            margin-bottom: 0.5rem;
        }

        .feature-text {
            font-size: 0.875rem;
            color: rgba(255, 255, 255, 0.8);
            line-height: 1.5;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

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

        @media (max-width: 768px) {
            .main-heading {
                font-size: 2.5rem;
            }

            .tagline {
                font-size: 1.25rem;
            }

            .subtitle {
                font-size: 1rem;
            }

            .button-group {
                flex-direction: column;
                align-items: stretch;
            }

            .btn {
                justify-content: center;
            }

            .features-section {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="hero-container">
        <!-- Decorative floating elements -->
        <div class="decoration decoration-1"></div>
        <div class="decoration decoration-2"></div>
        <div class="decoration decoration-3"></div>

        <div class="content-wrapper">
            <!-- Logo and University Name -->
            <div class="logo-section">
                <div class="logo-badge">
                    <img src="{{ asset('slsu-logo.png') }}" alt="SLSU Logo" class="logo-icon">
                </div>
                <div class="university-name">South Luzon State University</div>
                <div class="campus-name">Tiaong Campus</div>
            </div>

            <!-- Main Heading -->
            <h1 class="main-heading">Automated Scheduler</h1>
            
            <!-- Tagline -->
            <p class="tagline">Empowering Education Through Smart Scheduling</p>
            
            <!-- Subtitle -->
            <p class="subtitle">
                Experience seamless course management and efficient timetable generation. 
                Our intelligent system helps optimize your academic scheduling needs.
            </p>

            <!-- Call to Action Buttons -->
            <div class="button-group">
                @auth
                    <a href="{{ url('/dashboard') }}" class="btn btn-primary">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        Go to Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                        </svg>
                        Login
                    </a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="btn btn-secondary">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                            </svg>
                            Register
                        </a>
                    @endif
                @endauth
            </div>

            <!-- Features -->
            <div class="features-section">
                <div class="feature-card">
                    <svg class="feature-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    <h3 class="feature-title">Fast & Efficient</h3>
                    <p class="feature-text">Generate optimized schedules in seconds</p>
                </div>

                <div class="feature-card">
                    <svg class="feature-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h3 class="feature-title">Conflict-Free</h3>
                    <p class="feature-text">Automatic detection and resolution</p>
                </div>

                <div class="feature-card">
                    <svg class="feature-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h3 class="feature-title">Real-Time Updates</h3>
                    <p class="feature-text">Instant synchronization across devices</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>