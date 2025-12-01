<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SLSU Automated Scheduler - Tiaong Campus</title>

    <!-- Alpine.js -->
    <script src="//unpkg.com/alpinejs" defer></script>

    <style>
        [x-cloak] { display: none !important; }

        * { margin:0; padding:0; box-sizing:border-box; }
        body, html {
            width:100%; height:100%;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
            overflow-x:hidden;
        }

        .hero-container {
            position: relative;
            width: 100%;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            transition: filter .25s ease;
        }

        .blurred { filter: blur(4px) brightness(0.85); }

        .hero-container img.background-img {
            position: absolute; top:0; left:0;
            width: 100%; height: 100%;
            object-fit: cover;
            z-index: 0;
        }

        .hero-container::before {
            content: '';
            position: absolute; inset: 0;
            background: linear-gradient(135deg, rgba(12,59,46,0.8), rgba(109,151,115,0.6));
            z-index: 1;
        }

        .decoration {
            position: absolute;
            border-radius: 50%;
            opacity: 0.15;
            animation: float 6s ease-in-out infinite;
        }

        .decoration-1 { width:300px; height:300px; background-color:#FFBA00; top:10%; right:10%; animation-delay:0s; }
        .decoration-2 { width:200px; height:200px; background-color:#B46617; bottom:15%; left:8%; animation-delay:2s; }
        .decoration-3 { width:150px; height:150px; background-color:#6D9773; top:60%; right:15%; animation-delay:4s; }

        @keyframes float { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-20px); } }

        .content-wrapper {
            position: relative;
            z-index: 10;
            text-align: center;
            color: white;
            max-width: 900px;
            padding: 2rem;
        }

        .logo-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 120px; height: 120px;
            border-radius: 50%;
            border: 4px solid rgba(255,186,0,0.3);
            background: rgba(255,255,255,0.06);
            margin-bottom: 1rem;
            backdrop-filter: blur(10px);
        }

        .logo-badge img { width: 80px; height: 80px; object-fit: contain; }

        .university-name { font-weight: 600; font-size: 1.5rem; margin-bottom: 0.25rem; }
        .campus-name { font-weight: 400; font-size: 1rem; margin-bottom: 2rem; }

        .main-heading {
            font-size: 3rem; font-weight: 800; margin-bottom: 1rem;
            text-shadow: 0 4px 15px rgba(0,0,0,0.3);
            animation: fadeInUp 1s ease-out both;
        }

        .tagline {
            font-size: 1.25rem; font-weight: 500; margin-bottom: 2rem;
            animation: fadeInUp 1s ease-out 0.2s both;
        }

        .button-group {
            display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;
            animation: fadeInUp 1s ease-out 0.4s both;
        }

        .btn {
            padding: 0.75rem 2rem;
            border-radius: 0.75rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            transition: all .28s;
            cursor: pointer;
        }

        /* Glass Buttons */
        .btn-glass {
            background: rgba(255,255,255,0.06);
            color: #ffffff;
            border: 1px solid rgba(255,255,255,0.10);
            backdrop-filter: blur(8px);
        }

        :root { --hover-color: #FFBA00; }

        .btn-glass:hover {
            background: var(--hover-color);
            color: #0C3B2E !important;
            transform: translateY(-4px);
            box-shadow: 0 18px 40px rgba(0,0,0,0.18);
        }

        /* Modal */
        .modal-overlay {
            position: fixed; inset:0;
            display:flex; align-items:center; justify-content:center;
            z-index:50;
        }

        .modal-backdrop {
            position:absolute; inset:0;
            background: rgba(0,0,0,0.45);
            backdrop-filter: blur(4px);
        }

        .modal {
            position: relative;
            background: rgba(255,255,255,0.06);
            border:1px solid rgba(255,255,255,0.12);
            backdrop-filter: blur(12px);
            border-radius: 1rem;
            padding: 1.5rem 1.75rem;
            text-align: center;
            max-width: 22rem;
            width: 100%;
            z-index: 60;
            box-shadow: 0 10px 40px rgba(3,10,10,0.6);
        }

        .modal h2 { font-size:1.25rem; font-weight:700; color:white; margin-bottom:0.5rem; }
        .modal p { color: #e9f2ea; margin-bottom:1rem; font-size:0.95rem; }

        .role-btn {
            margin:0.45rem 0;
            padding:0.75rem;
            border-radius:0.75rem;
            font-weight:700;
            border:1px solid rgba(255,255,255,0.08);
            color: #fff;
            text-decoration: none;
            background: rgba(255,255,255,0.04);
            display:block;
            transition: all .28s;
        }

        .role-btn:hover {
            background: var(--hover-color);
            color: #0C3B2E !important;
            transform: translateY(-3px);
        }

        .close-btn {
            margin-top: 0.75rem;
            background: #ffffff;
            color: #0C3B2E;
            padding: 0.6rem 1.4rem;
            border-radius: 0.75rem;
            font-weight: 700;
            border: none;
        }

        .close-btn:hover {
            background: var(--hover-color);
            transform: translateY(-3px);
        }

        @keyframes fadeInUp {
            from { opacity:0; transform:translateY(30px); }
            to { opacity:1; transform:translateY(0); }
        }
    </style>
</head>

<body x-data="heroPage()" class="antialiased">

    <!-- HERO SECTION -->
    <div :class="showRoleModal ? 'hero-container blurred' : 'hero-container'">
        <img src="{{ asset('automated.png') }}" class="background-img">

        <div class="decoration decoration-1"></div>
        <div class="decoration decoration-2"></div>
        <div class="decoration decoration-3"></div>

        <div class="content-wrapper">
            <div class="logo-badge">
                <img src="{{ asset('slsu-logo.png') }}" alt="SLSU Logo">
            </div>

            <div class="university-name">South Luzon State University</div>
            <div class="campus-name">Tiaong Campus</div>

            <h1 class="main-heading">Automated Scheduler</h1>
            <p class="tagline">Empowering Education Through Smart Scheduling</p>

            <div class="button-group">
                @auth
                    <a href="{{ url('/home') }}" class="btn btn-glass">Go to Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-glass">Login</a>

                    @if(Route::has('register'))
                        <button @click="showRoleModal = true" class="btn btn-glass">Register</button>
                    @endif
                @endauth
            </div>
        </div>
    </div>

    <!-- ROLE SELECT MODAL -->
    <div x-show="showRoleModal" x-cloak x-transition.opacity.duration.250ms class="modal-overlay">
        <div x-cloak class="modal-backdrop" @click="showRoleModal = false"></div>

        <div x-cloak class="modal" @click.stop x-transition.scale.duration.300ms>
            <h2>Select Your Role</h2>
            <p>Choose your role before registering.</p>

            <a href="{{ route('register', ['role' => 'admin']) }}" class="role-btn">Admin</a>
            <a href="{{ route('register', ['role' => 'faculty']) }}" class="role-btn">Faculty</a>

            <button class="close-btn" @click="showRoleModal = false">Close</button>
        </div>
    </div>

    <script>
        function heroPage() {
            return {
                showRoleModal: false
            }
        }
    </script>

</body>
</html>
