<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SLSU Automated Scheduler - Tiaong Campus</title>
    <script src="//unpkg.com/alpinejs" defer></script>
    <style>
        [x-cloak] { display: none !important; }

        * { margin:0; padding:0; box-sizing:border-box; }
        
        body, html {
            width:100%; height:100%;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Inter', Arial, sans-serif;
            overflow-x:hidden;
            background: #0a0a0a;
        }

        .hero-container {
            position: relative;
            width: 100%;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            transition: all .4s cubic-bezier(0.4, 0, 0.2, 1);
            padding: 4rem 2rem;
        }

        .blurred { 
            filter: blur(8px) brightness(0.7);
            transform: scale(1.02);
        }

        .hero-container img.background-img {
            position: absolute;
            top:0; left:0;
            width: 100%; 
            height: 100%;
            object-fit: cover;
            z-index: 0;
            animation: slowZoom 20s ease-in-out infinite alternate;
        }

        @keyframes slowZoom {
            from { transform: scale(1); }
            to { transform: scale(1.05); }
        }

        .hero-container::before {
            content: '';
            position: absolute;
            inset: 0;
            background: 
                radial-gradient(circle at 20% 80%, rgba(255,186,0,0.15) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(109,151,115,0.15) 0%, transparent 50%),
                linear-gradient(135deg, rgba(12,59,46,0.92), rgba(10,10,10,0.85));
            z-index: 1;
        }

        .particles {
            position: absolute;
            inset: 0;
            z-index: 2;
            overflow: hidden;
        }

        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: rgba(255,186,0,0.6);
            border-radius: 50%;
            animation: float-particle 15s ease-in-out infinite;
            box-shadow: 0 0 10px currentColor;
        }

        .particle:nth-child(1) { left: 10%; top: 20%; animation-delay: 0s; animation-duration: 12s; }
        .particle:nth-child(2) { left: 80%; top: 30%; animation-delay: 3s; animation-duration: 15s; }
        .particle:nth-child(3) { left: 30%; top: 70%; animation-delay: 6s; background: rgba(109,151,115,0.6); animation-duration: 18s; }
        .particle:nth-child(4) { left: 70%; top: 60%; animation-delay: 9s; animation-duration: 14s; }
        .particle:nth-child(5) { left: 50%; top: 40%; animation-delay: 12s; background: rgba(180,102,23,0.6); animation-duration: 16s; }
        .particle:nth-child(6) { left: 20%; top: 50%; animation-delay: 4s; animation-duration: 13s; }
        .particle:nth-child(7) { left: 90%; top: 70%; animation-delay: 7s; background: rgba(109,151,115,0.6); animation-duration: 17s; }
        .particle:nth-child(8) { left: 60%; top: 15%; animation-delay: 10s; animation-duration: 11s; }

        @keyframes float-particle {
            0%, 100% { transform: translate(0, 0) scale(1); opacity: 0; }
            10%, 90% { opacity: 1; }
            50% { transform: translate(30px, -50px) scale(1.5); }
        }

        .decoration {
            position: absolute;
            border-radius: 50%;
            opacity: 0.08;
            filter: blur(60px);
            animation: float-decoration 8s ease-in-out infinite;
        }

        .decoration-1 { 
            width:400px; height:400px; 
            background: radial-gradient(circle, #FFBA00, transparent);
            top:5%; right:5%; 
            animation-delay:0s; 
        }
        .decoration-2 { 
            width:350px; height:350px; 
            background: radial-gradient(circle, #B46617, transparent);
            bottom:10%; left:5%; 
            animation-delay:3s; 
        }
        .decoration-3 { 
            width:300px; height:300px; 
            background: radial-gradient(circle, #6D9773, transparent);
            top:50%; right:10%; 
            animation-delay:6s; 
        }

        @keyframes float-decoration { 
            0%,100% { transform: translate(0, 0) scale(1); } 
            50% { transform: translate(-30px, -40px) scale(1.1); } 
        }

        .content-wrapper {
            position: relative;
            z-index: 10;
            text-align: center;
            color: white;
            max-width: 1200px;
            width: 100%;
            padding: 2rem;
            animation: fadeInScale 1.2s cubic-bezier(0.4, 0, 0.2, 1) both;
        }

        @keyframes fadeInScale {
            from { opacity:0; transform: scale(0.95) translateY(20px); }
            to { opacity:1; transform: scale(1) translateY(0); }
        }

        .logo-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 160px; 
            height: 160px;
            border-radius: 50%;
            border: 4px solid rgba(255,186,0,0.5);
            background: linear-gradient(135deg, rgba(255,255,255,0.12), rgba(255,186,0,0.08));
            margin-bottom: 1.5rem;
            backdrop-filter: blur(24px);
            position: relative;
            animation: logo-float 4s ease-in-out infinite, pulse-glow 3s ease-in-out infinite;
            box-shadow: 
                0 0 30px rgba(255,186,0,0.3),
                inset 0 2px 4px rgba(255,255,255,0.2),
                inset 0 -2px 4px rgba(0,0,0,0.2);
        }

        .logo-badge::before {
            content: '';
            position: absolute;
            inset: -15px;
            border-radius: 50%;
            background: conic-gradient(
                rgba(255,186,0,0.3),
                rgba(109,151,115,0.3),
                rgba(180,102,23,0.3),
                rgba(255,186,0,0.3)
            );
            filter: blur(20px);
            animation: rotate-glow 8s linear infinite;
            opacity: 0.7;
        }

        .logo-badge::after {
            content: '';
            position: absolute;
            inset: 8px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255,186,0,0.1), transparent);
            animation: pulse-inner 2s ease-in-out infinite;
        }

        @keyframes logo-float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            25% { transform: translateY(-10px) rotate(2deg); }
            50% { transform: translateY(-15px) rotate(0deg); }
            75% { transform: translateY(-10px) rotate(-2deg); }
        }

        @keyframes pulse-glow {
            0%, 100% { 
                box-shadow: 
                    0 0 30px rgba(255,186,0,0.3),
                    inset 0 2px 4px rgba(255,255,255,0.2),
                    inset 0 -2px 4px rgba(0,0,0,0.2);
            }
            50% { 
                box-shadow: 
                    0 0 60px rgba(255,186,0,0.6),
                    0 0 90px rgba(255,186,0,0.3),
                    inset 0 2px 6px rgba(255,255,255,0.3),
                    inset 0 -2px 6px rgba(0,0,0,0.2);
            }
        }

        @keyframes rotate-glow {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        @keyframes pulse-inner {
            0%, 100% { opacity: 0.3; transform: scale(0.9); }
            50% { opacity: 0.6; transform: scale(1.1); }
        }

        .logo-badge img { 
            width: 110px; 
            height: 110px; 
            object-fit: cover;
            border-radius: 50%;
            position: relative;
            z-index: 1;
            filter: drop-shadow(0 8px 16px rgba(0,0,0,0.4));
            animation: logo-spin 20s linear infinite;
            border: 3px solid rgba(255,255,255,0.2);
        }

        @keyframes logo-spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .logo-badge:hover img {
            animation: logo-spin 3s linear infinite;
        }

        .university-name { 
            font-weight: 700; 
            font-size: 1.75rem; 
            margin-bottom: 0.5rem;
            letter-spacing: -0.02em;
            background: linear-gradient(135deg, #ffffff, #e8e8e8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .campus-name { 
            font-weight: 500; 
            font-size: 1.1rem; 
            margin-bottom: 2.5rem;
            color: rgba(255,255,255,0.8);
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        .main-heading {
            font-size: 4rem; 
            font-weight: 900; 
            margin-bottom: 1.25rem;
            background: linear-gradient(135deg, #ffffff 0%, #FFBA00 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -0.03em;
            line-height: 1.1;
            position: relative;
            animation: fadeInUp 1s ease-out 0.3s both, text-glow 3s ease-in-out infinite;
        }

        .main-heading::before {
            content: 'Automated Scheduler';
            position: absolute;
            left: 0;
            right: 0;
            top: 0;
            background: linear-gradient(135deg, #FFBA00 0%, #FFD700 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            filter: blur(20px);
            opacity: 0.5;
            z-index: -1;
            animation: pulse-text 3s ease-in-out infinite;
        }

        @keyframes text-glow {
            0%, 100% { 
                filter: drop-shadow(0 0 10px rgba(255,186,0,0.3));
            }
            50% { 
                filter: drop-shadow(0 0 30px rgba(255,186,0,0.6));
            }
        }

        @keyframes pulse-text {
            0%, 100% { opacity: 0.3; transform: scale(1); }
            50% { opacity: 0.6; transform: scale(1.05); }
        }

        .tagline {
            font-size: 1.4rem; 
            font-weight: 400; 
            margin-bottom: 1rem;
            color: rgba(255,255,255,0.9);
            letter-spacing: 0.01em;
            animation: fadeInUp 1s ease-out 0.5s both;
        }

        .tagline-info {
            font-size: 1rem;
            font-weight: 300;
            color: rgba(255,255,255,0.7);
            max-width: 700px;
            margin: 0 auto 2.5rem;
            line-height: 1.7;
            animation: fadeInUp 1s ease-out 0.6s both;
            padding: 0 1rem;
        }

        .tagline-info::before,
        .tagline-info::after {
            content: '•';
            display: inline-block;
            margin: 0 0.75rem;
            color: rgba(255,186,0,0.6);
            animation: blink 2s ease-in-out infinite;
        }

        @keyframes blink {
            0%, 100% { opacity: 0.3; }
            50% { opacity: 1; }
        }

        .button-group {
            display: flex; 
            gap: 1.25rem; 
            justify-content: center; 
            flex-wrap: wrap;
            animation: fadeInUp 1s ease-out 0.7s both;
            margin-bottom: 4rem;
        }

        /* Feature Cards */
        .features-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            max-width: 1100px;
            margin: 0 auto;
            animation: fadeInUp 1.2s ease-out 0.9s both;
        }

        .feature-card {
            position: relative;
            background: rgba(255,255,255,0.06);
            backdrop-filter: blur(20px);
            border: 2px solid rgba(255,255,255,0.1);
            border-radius: 1.5rem;
            padding: 2.5rem 2rem;
            text-align: center;
            transition: all .5s cubic-bezier(0.34, 1.56, 0.64, 1);
            overflow: hidden;
            box-shadow: 
                0 8px 32px rgba(0,0,0,0.3),
                inset 0 1px 0 rgba(255,255,255,0.1);
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            transition: left .7s;
        }

        .feature-card:hover::before {
            left: 100%;
        }

        .feature-card::after {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 1.5rem;
            padding: 2px;
            background: linear-gradient(135deg, rgba(255,186,0,0.4), transparent, rgba(109,151,115,0.4));
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            opacity: 0;
            transition: opacity .5s;
        }

        .feature-card:hover::after {
            opacity: 1;
        }

        .feature-card:hover {
            transform: translateY(-12px) scale(1.02);
            background: rgba(255,255,255,0.1);
            border-color: rgba(255,186,0,0.4);
            box-shadow: 
                0 24px 60px rgba(255,186,0,0.3),
                0 0 80px rgba(255,186,0,0.2),
                inset 0 1px 0 rgba(255,255,255,0.2);
        }

        .feature-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            background: linear-gradient(135deg, rgba(255,186,0,0.2), rgba(109,151,115,0.2));
            border-radius: 1.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            position: relative;
            box-shadow: 
                0 8px 24px rgba(0,0,0,0.3),
                inset 0 1px 0 rgba(255,255,255,0.2);
            transition: all .5s cubic-bezier(0.34, 1.56, 0.64, 1);
            border: 2px solid rgba(255,186,0,0.3);
        }

        .feature-card:hover .feature-icon {
            transform: rotateY(360deg) scale(1.1);
            background: linear-gradient(135deg, rgba(255,186,0,0.4), rgba(109,151,115,0.4));
            box-shadow: 
                0 12px 32px rgba(255,186,0,0.4),
                inset 0 2px 0 rgba(255,255,255,0.3);
        }

        .feature-icon::before {
            content: '';
            position: absolute;
            inset: -8px;
            border-radius: 1.5rem;
            background: radial-gradient(circle, rgba(255,186,0,0.3), transparent);
            filter: blur(12px);
            opacity: 0;
            transition: opacity .5s;
        }

        .feature-card:hover .feature-icon::before {
            opacity: 1;
        }

        .feature-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 0.75rem;
            letter-spacing: -0.02em;
            transition: all .4s;
        }

        .feature-card:hover .feature-title {
            background: linear-gradient(135deg, #ffffff, #FFBA00);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            transform: scale(1.05);
        }

        .feature-description {
            font-size: 0.95rem;
            color: rgba(255,255,255,0.75);
            line-height: 1.6;
            transition: color .4s;
        }

        .feature-card:hover .feature-description {
            color: rgba(255,255,255,0.9);
        }

        .btn {
            padding: 1rem 2.5rem;
            border-radius: 1rem;
            font-weight: 700;
            font-size: 1.05rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: all .4s cubic-bezier(0.34, 1.56, 0.64, 1);
            cursor: pointer;
            position: relative;
            overflow: hidden;
            border: none;
        }

        .btn svg {
            transition: transform .4s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .btn:hover svg {
            transform: translateX(4px);
        }

        .btn::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.2), transparent);
            opacity: 0;
            transition: opacity .4s;
        }

        .btn::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255,255,255,0.3);
            transform: translate(-50%, -50%);
            transition: width .6s, height .6s;
        }

        .btn:hover::before {
            opacity: 1;
        }

        .btn:active::after {
            width: 300px;
            height: 300px;
            transition: width .01s, height .01s;
        }

        .btn-glass {
            background: rgba(255,255,255,0.08);
            color: #ffffff;
            border: 2px solid rgba(255,255,255,0.2);
            backdrop-filter: blur(16px);
            box-shadow: 
                0 8px 32px rgba(0,0,0,0.3),
                inset 0 1px 0 rgba(255,255,255,0.1),
                inset 0 -1px 0 rgba(0,0,0,0.1);
        }

        .btn-glass:hover {
            background: linear-gradient(135deg, #FFBA00, #FFD700);
            color: #0C3B2E !important;
            transform: translateY(-6px) scale(1.05);
            box-shadow: 
                0 24px 48px rgba(255,186,0,0.5),
                0 0 0 1px rgba(255,186,0,0.5),
                inset 0 1px 0 rgba(255,255,255,0.3);
            border-color: #FFBA00;
        }

        .btn-primary {
            background: rgba(255,186,0,0.15);
            border-color: rgba(255,186,0,0.4);
        }

        .btn-glass:active {
            transform: translateY(-2px) scale(1.02);
            transition: transform .1s;
        }

        /* Modal */
        .modal-overlay {
            position: fixed; 
            inset:0;
            display:flex; 
            align-items:center; 
            justify-content:center;
            z-index:50;
            perspective: 1000px;
        }

        .modal-backdrop {
            position:absolute; 
            inset:0;
            background: rgba(0,0,0,0.7);
            backdrop-filter: blur(12px);
            animation: backdropFade 0.4s ease-out;
        }

        @keyframes backdropFade {
            from { opacity: 0; backdrop-filter: blur(0px); }
            to { opacity: 1; backdrop-filter: blur(12px); }
        }

        .modal {
            position: relative;
            background: rgba(255,255,255,0.08);
            border: 2px solid rgba(255,186,0,0.3);
            backdrop-filter: blur(24px);
            border-radius: 2rem;
            padding: 3rem 2.5rem;
            text-align: center;
            max-width: 28rem;
            width: 90%;
            z-index: 60;
            box-shadow: 
                0 24px 80px rgba(0,0,0,0.9),
                0 0 0 1px rgba(255,255,255,0.1) inset,
                0 0 100px rgba(255,186,0,0.2);
            transform-style: preserve-3d;
        }

        .modal::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 2rem;
            padding: 2px;
            background: linear-gradient(135deg, rgba(255,186,0,0.5), transparent, rgba(109,151,115,0.5));
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            opacity: 0.6;
            animation: rotateBorder 3s linear infinite;
        }

        @keyframes rotateBorder {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .modal h2 { 
            font-size:2rem; 
            font-weight:800; 
            background: linear-gradient(135deg, #ffffff, #FFBA00);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom:1rem;
            letter-spacing: -0.02em;
            animation: slideInDown 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) 0.2s both;
        }

        .modal p { 
            color: rgba(255,255,255,0.8); 
            margin-bottom:2rem; 
            font-size:1.05rem;
            animation: slideInDown 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) 0.3s both;
        }

        @keyframes slideInDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .role-btn {
            margin:0.75rem 0;
            padding:1.25rem 1.5rem;
            border-radius:1.25rem;
            font-weight:700;
            font-size: 1.1rem;
            border:2px solid rgba(255,186,0,0.3);
            color: #fff;
            text-decoration: none;
            background: rgba(255,255,255,0.08);
            backdrop-filter: blur(8px);
            display:flex;
            align-items: center;
            justify-content: space-between;
            transition: all .4s cubic-bezier(0.34, 1.56, 0.64, 1);
            position: relative;
            overflow: hidden;
            box-shadow: 
                0 4px 16px rgba(0,0,0,0.2),
                inset 0 1px 0 rgba(255,255,255,0.1);
        }

        .role-btn:nth-child(3) {
            animation: slideInLeft 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) 0.4s both;
        }

        .role-btn:nth-child(4) {
            animation: slideInLeft 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) 0.5s both;
        }

        @keyframes slideInLeft {
            from { opacity: 0; transform: translateX(-30px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .role-btn::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255,186,0,0.15), transparent);
            transform: translateX(-100%) skewX(-15deg);
            transition: transform .5s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .role-btn::after {
            content: '→';
            font-size: 1.5rem;
            opacity: 0;
            transform: translateX(-10px);
            transition: all .4s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .role-btn:hover::before {
            transform: translateX(0) skewX(-15deg);
        }

        .role-btn:hover::after {
            opacity: 1;
            transform: translateX(0);
        }

        .role-btn:hover {
            background: linear-gradient(135deg, #FFBA00, #FFD700);
            color: #0C3B2E !important;
            transform: translateX(10px) scale(1.03);
            border-color: #FFBA00;
            box-shadow: 
                0 16px 40px rgba(255,186,0,0.5),
                0 0 0 1px rgba(255,186,0,0.5),
                inset 0 1px 0 rgba(255,255,255,0.3);
        }

        .role-btn:active {
            transform: translateX(8px) scale(1);
            transition: transform .1s;
        }

        .close-btn {
            margin-top: 1.5rem;
            background: rgba(255,255,255,0.08);
            color: #ffffff;
            padding: 0.9rem 2rem;
            border-radius: 1.25rem;
            font-weight: 700;
            border: 2px solid rgba(255,255,255,0.2);
            cursor: pointer;
            transition: all .4s cubic-bezier(0.34, 1.56, 0.64, 1);
            backdrop-filter: blur(8px);
            box-shadow: 0 4px 16px rgba(0,0,0,0.2);
            animation: slideInUp 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) 0.6s both;
        }

        @keyframes slideInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .close-btn:hover {
            background: linear-gradient(135deg, #FFBA00, #FFD700);
            color: #0C3B2E !important;
            transform: translateY(-6px) scale(1.05);
            border-color: #FFBA00; 
            box-shadow: 0 16px 40px rgba(255,186,0,0.5),
            0 0 0 1px rgba(255,186,0,0.5),
            inset 0 1px 0 rgba(255,255,255,0.3);
        }

        .close-btn:active {
            transform: translateY(-1px) scale(1.02);
            transition: transform .1s;
        }

        @keyframes fadeInUp {
            from { opacity:0; transform:translateY(30px); }
            to { opacity:1; transform:translateY(0); }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main-heading { font-size: 2.5rem; }
            .tagline { font-size: 1.1rem; }
            .tagline-info { font-size: 0.9rem; }
            .university-name { font-size: 1.4rem; }
            .logo-badge { width: 130px; height: 130px; }
            .logo-badge img { width: 85px; height: 85px; }
            .btn { padding: 0.85rem 1.75rem; font-size: 0.95rem; }
            .features-container {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
            .feature-card {
                padding: 2rem 1.5rem;
            }
        }

        /* Smooth scroll */
        html { scroll-behavior: smooth; }
    </style>
</head>

<body x-data="heroPage()" class="antialiased">

    <!-- HERO SECTION -->
    <div :class="showRoleModal ? 'hero-container blurred' : 'hero-container'">
        <img src="automated.png" class="background-img" alt="Background">

        <div class="particles">
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
        </div>

        <div class="decoration decoration-1"></div>
        <div class="decoration decoration-2"></div>
        <div class="decoration decoration-3"></div>

        <div class="content-wrapper">
            <div class="logo-badge">
                <img src="slsu-logo.png" alt="SLSU Logo">
            </div>

            <div class="university-name">South Luzon State University</div>
            <div class="campus-name">Tiaong Campus</div>

            <h1 class="main-heading">Automated Scheduler</h1>
            <p class="tagline">Empowering Education Through Smart Scheduling</p>
            <p class="tagline-info">Transform your academic workflow with intelligent automation, seamless coordination, and real-time insights for optimal educational excellence</p>

            <div class="button-group">
                <a href="{{ route('login') }}" class="btn btn-glass">
                    <span>Login</span>
                </a>
                <button @click="showRoleModal = true" class="btn btn-glass btn-primary">
                    <span>Register</span>
                </button>
            </div>

            <!-- Feature Cards -->
            <div class="features-container">
                <div class="feature-card">
                    <div class="feature-icon">⚡</div>
                    <h3 class="feature-title">Smart Scheduling</h3>
                    <p class="feature-description">AI-powered algorithms optimize class schedules, reducing conflicts and maximizing resource utilization</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">🎯</div>
                    <h3 class="feature-title">Real-time Updates</h3>
                    <p class="feature-description">Instant notifications and live schedule changes keep everyone informed and synchronized</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">📊</div>
                    <h3 class="feature-title">Analytics Dashboard</h3>
                    <p class="feature-description">Comprehensive insights and reports to help make data-driven scheduling decisions</p>
                </div>
            </div>
        </div>
    </div>

    <!-- ROLE SELECT MODAL -->
    <div x-show="showRoleModal" x-cloak class="modal-overlay">
        <div x-cloak 
             class="modal-backdrop" 
             @click="showRoleModal = false"
             x-transition:enter="transition ease-out duration-400"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
        </div>

        <div x-cloak 
             class="modal" 
             @click.stop
             x-transition:enter="transition ease-out duration-500"
             x-transition:enter-start="opacity-0 transform scale-75 -translate-y-8"
             x-transition:enter-end="opacity-100 transform scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100 transform scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 transform scale-90 translate-y-4">
            <h2>Select Your Role</h2>
            <p>Choose your role to begin registration</p>

            <a href="{{ route('register.admin') }}" class="role-btn">
                <span>👤 Admin</span>
            </a>
            <a href="{{ route('register.faculty') }}" class="role-btn">
                <span>🎓 Faculty</span>
            </a>

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