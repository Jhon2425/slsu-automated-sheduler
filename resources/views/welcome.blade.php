<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SLSU Automated Scheduler - Tiaong Campus</title>

    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body, html { width:100%; height:100%; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; overflow-x:hidden; }

        .hero-container {
            position: relative;
            width: 100%;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        /* Background Image */
        .hero-container img.background-img {
            position: absolute;
            top:0;
            left:0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: 0;
            transition: all 0.5s ease;
        }

        /* Gradient overlay */
        .hero-container::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(12,59,46,0.8), rgba(109,151,115,0.6));
            z-index: 1;
            transition: all 0.5s ease;
        }

        /* Floating Decorations */
        .decoration {
            position: absolute;
            border-radius: 50%;
            opacity: 0.15;
            animation: float 6s ease-in-out infinite;
        }

        .decoration-1 { width:300px; height:300px; background-color:#FFBA00; top:10%; right:10%; animation-delay:0s; }
        .decoration-2 { width:200px; height:200px; background-color:#B46617; bottom:15%; left:8%; animation-delay:2s; }
        .decoration-3 { width:150px; height:150px; background-color:#6D9773; top:60%; right:15%; animation-delay:4s; }

        @keyframes float {
            0%,100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        .content-wrapper {
            position: relative;
            z-index: 10;
            text-align: center;
            color: white;
            max-width: 900px;
            width: 100%;
            padding: 2rem;
            transition: all 0.5s ease;
        }

        .logo-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 4px solid rgba(255,186,0,0.3);
            background: rgba(255,255,255,0.1);
            margin-bottom: 1rem;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }

        .logo-badge img {
            width: 80px;
            height: 80px;
            object-fit: contain;
            border-radius: 50%;
        }

        .university-name {
            font-weight: 600;
            font-size: 1.5rem;
            margin-bottom: 0.25rem;
            transition: all 0.3s ease;
        }

        .campus-name {
            font-weight: 400;
            font-size: 1rem;
            margin-bottom: 2rem;
            transition: all 0.3s ease;
        }

        .main-heading {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 1rem;
            text-shadow: 0 4px 15px rgba(0,0,0,0.3);
            animation: fadeInUp 1s ease-out both;
        }

        .tagline {
            font-size: 1.25rem;
            font-weight: 500;
            margin-bottom: 2rem;
            animation: fadeInUp 1s ease-out 0.2s both;
        }

        .button-group {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            animation: fadeInUp 1s ease-out 0.4s both;
        }

        .btn {
            padding: 0.75rem 2rem;
            border-radius: 0.75rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #FFBA00, #B46617);
            color: #0C3B2E;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(255,186,0,0.4);
        }

        .btn-secondary {
            background: rgba(255,255,255,0.15);
            color: white;
            border: 1px solid rgba(255,255,255,0.3);
            backdrop-filter: blur(10px);
        }

        .btn-secondary:hover {
            background: rgba(255,255,255,0.25);
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(255,255,255,0.2);
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width:768px){
            .main-heading { font-size:2rem; }
            .tagline { font-size:1rem; }
        }
    </style>
</head>
<body>
    <div class="hero-container">
        <!-- Background Image -->
        <img src="{{ asset('automated.png') }}" alt="Background" class="background-img">

        <!-- Decorative floating elements -->
        <div class="decoration decoration-1"></div>
        <div class="decoration decoration-2"></div>
        <div class="decoration decoration-3"></div>

        <div class="content-wrapper">
            <!-- Logo and University Name -->
            <div class="logo-badge">
                <img src="{{ asset('slsu-logo.png') }}" alt="SLSU Logo">
            </div>
            <div class="university-name">South Luzon State University</div>
            <div class="campus-name">Tiaong Campus</div>

            <!-- Main Heading -->
            <h1 class="main-heading">Automated Scheduler</h1>

            <!-- Tagline -->
            <p class="tagline">Empowering Education Through Smart Scheduling</p>

            <!-- Buttons -->
            <div class="button-group">
                @auth
                    <a href="{{ url('/dashboard') }}" class="btn btn-primary">Go to Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary">Login</a>
                    @if(Route::has('register'))
                        <a href="{{ route('register') }}" class="btn btn-secondary">Register</a>
                    @endif
                @endauth
            </div>
        </div>
    </div>
</body>
</html>
