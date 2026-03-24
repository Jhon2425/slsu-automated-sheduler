<x-app-layout>
    <div class="min-h-screen bg-[url('/path/to/your/bg.jpg')] bg-cover bg-center py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Breadcrumb -->
            <nav class="flex items-center text-sm text-white/80 mb-6">
                <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('faculty.dashboard') }}"
                   class="flex items-center hover:text-violet-400 transition-colors">
                    <i class="fas fa-home mr-2"></i>
                    Dashboard
                </a>
                <i class="fas fa-chevron-right mx-3 text-xs text-white/50"></i>
                <span class="font-semibold text-white">Profile</span>
            </nav>

            <!-- Page Header -->
            <div class="glass-card rounded-2xl shadow-xl p-8 mb-8 border border-white/20">
                <div class="flex items-center gap-4">
                    <div class="bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl p-4 shadow-lg">
                        <i class="fas fa-user-circle text-white text-4xl"></i>
                    </div>
                    <div>
                        <h1 class="text-4xl font-bold text-white drop-shadow-lg">
                            Profile Settings
                        </h1>
                        <p class="mt-2 text-white/90 text-lg drop-shadow">Manage your account information and security</p>
                    </div>
                </div>
            </div>

            <!-- Profile Information Section -->
            <div class="glass-card rounded-2xl shadow-xl p-8 mb-6 border border-white/20">
                <div class="max-w-2xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <!-- Update Password Section -->
            <div class="glass-card rounded-2xl shadow-xl p-8 mb-6 border border-white/20">
                <div class="max-w-2xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <!-- Delete Account Section -->
            <div class="glass-card rounded-2xl shadow-xl p-8 border border-white/20 border-red-500/30">
                <div class="max-w-2xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>

        </div>
    </div>

    <style>
        /* Glass Card Effect */
        .glass-card {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        /* Style the form sections */
        .glass-card header h2 {
            color: white;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .glass-card header p {
            color: rgba(255, 255, 255, 0.8);
        }

        /* Input styling */
        .glass-card input[type="text"],
        .glass-card input[type="email"],
        .glass-card input[type="password"] {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
        }

        .glass-card input[type="text"]:focus,
        .glass-card input[type="email"]:focus,
        .glass-card input[type="password"]:focus {
            outline: none;
            border-color: rgba(96, 165, 250, 0.5);
            box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.1);
        }

        .glass-card input::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        /* Label styling */
        .glass-card label {
            color: rgba(255, 255, 255, 0.9);
            font-weight: 600;
            font-size: 0.875rem;
            display: block;
            margin-bottom: 0.5rem;
        }

        /* Button styling */
        .glass-card button[type="submit"],
        .glass-card .primary-button {
            background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 0.75rem;
            font-weight: 600;
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .glass-card button[type="submit"]:hover,
        .glass-card .primary-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(59, 130, 246, 0.4);
        }

        /* Danger button (delete account) */
        .glass-card button.danger,
        .glass-card .danger-button {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }

        .glass-card button.danger:hover,
        .glass-card .danger-button:hover {
            box-shadow: 0 10px 25px rgba(239, 68, 68, 0.4);
        }

        /* Error messages */
        .glass-card .text-red-600 {
            color: #fca5a5 !important;
        }

        /* Success messages */
        .glass-card .text-green-600 {
            color: #86efac !important;
        }

        /* Status messages */
        .glass-card .text-gray-600 {
            color: rgba(255, 255, 255, 0.7) !important;
        }

        .glass-card .text-gray-800 {
            color: rgba(255, 255, 255, 0.9) !important;
        }

        /* Form spacing */
        .glass-card form {
            margin-top: 1.5rem;
        }

        .glass-card form > div {
            margin-bottom: 1.5rem;
        }

        /* Underline links */
        .glass-card a.underline {
            color: #93c5fd;
            text-decoration: underline;
        }

        .glass-card a.underline:hover {
            color: #60a5fa;
        }
    </style>
</x-app-layout>