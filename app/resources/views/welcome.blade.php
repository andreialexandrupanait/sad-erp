<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $globalAppSettings['app_name'] ?? config('app.name', 'Laravel') }}</title>

        <!-- Favicon -->
        @if(isset($globalAppSettings['app_favicon']) && $globalAppSettings['app_favicon'])
            <link rel="icon" type="image/x-icon" href="{{ asset('storage/' . $globalAppSettings['app_favicon']) }}">
        @endif

        <!-- Fonts - Inter for modern clean look -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Tailwind CSS CDN -->
        <script src="https://cdn.tailwindcss.com"></script>

        <!-- Custom Primary Color CSS -->
        <style>
            :root {
                --primary-color: {{ $globalAppSettings['primary_color'] ?? '#3b82f6' }};
            }

            /* Override primary color classes */
            .bg-primary-600 { background-color: var(--primary-color) !important; }
            .hover\:bg-primary-700:hover { filter: brightness(0.9); background-color: var(--primary-color) !important; }
            .text-primary-600 { color: var(--primary-color) !important; }
            .hover\:text-primary-700:hover { filter: brightness(0.9); color: var(--primary-color) !important; }
            .border-primary-600 { border-color: var(--primary-color) !important; }
            .ring-primary-500 { --tw-ring-color: var(--primary-color) !important; }

            /* Gradient background */
            .gradient-bg {
                background: linear-gradient(135deg, var(--primary-color) 0%, #1e3a8a 100%);
            }

            /* Animated gradient */
            .animated-gradient {
                background: linear-gradient(135deg,
                    var(--primary-color) 0%,
                    #1e3a8a 50%,
                    var(--primary-color) 100%);
                background-size: 200% 200%;
                animation: gradientShift 15s ease infinite;
            }

            @keyframes gradientShift {
                0%, 100% { background-position: 0% 50%; }
                50% { background-position: 100% 50%; }
            }

            /* Glass morphism */
            .glass {
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(10px);
            }

            /* Feature card hover effect */
            .feature-card {
                transition: transform 0.3s ease, box-shadow 0.3s ease;
            }
            .feature-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            }
        </style>
    </head>
    <body class="font-sans antialiased">
        <!-- Navigation -->
        <nav class="absolute top-0 left-0 right-0 z-50 px-4 py-6 md:px-8">
            <div class="max-w-7xl mx-auto flex items-center justify-between">
                <!-- Logo -->
                <div class="flex items-center gap-3">
                    @if(isset($globalAppSettings['app_logo']) && $globalAppSettings['app_logo'])
                        <img src="{{ asset('storage/' . $globalAppSettings['app_logo']) }}"
                             alt="{{ $globalAppSettings['app_name'] ?? 'Logo' }}"
                             class="h-10 w-auto">
                    @else
                        <div class="flex items-center justify-center w-10 h-10 bg-white rounded-lg shadow-lg">
                            <svg class="w-6 h-6 text-primary-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5z"/>
                            </svg>
                        </div>
                    @endif
                    <span class="text-xl font-bold text-white">
                        {{ $globalAppSettings['app_name'] ?? config('app.name', 'Laravel') }}
                    </span>
                </div>

                <!-- Auth Links -->
                @if (Route::has('login'))
                    <div class="flex items-center gap-4">
                        @auth
                            <a href="{{ url('/dashboard') }}"
                               class="px-6 py-2.5 bg-white text-primary-600 font-semibold rounded-lg hover:bg-gray-50 transition shadow-lg">
                                {{ __('Dashboard') }}
                            </a>
                        @else
                            <a href="{{ route('login') }}"
                               class="px-4 py-2 text-white hover:text-blue-100 transition">
                                {{ __('Log in') }}
                            </a>

                            @if (Route::has('register'))
                                <a href="{{ route('register') }}"
                                   class="px-6 py-2.5 bg-white text-primary-600 font-semibold rounded-lg hover:bg-gray-50 transition shadow-lg">
                                    {{ __('Sign up') }}
                                </a>
                            @endif
                        @endauth
                    </div>
                @endif
            </div>
        </nav>

        <!-- Hero Section -->
        <div class="animated-gradient min-h-screen flex items-center justify-center relative overflow-hidden">
            <!-- Background Pattern -->
            <div class="absolute inset-0 opacity-10">
                <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'1\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
            </div>

            <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center">
                <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-white mb-6 leading-tight">
                    {{ __('Streamline Your Business') }}<br>
                    <span class="text-blue-200">{{ __('Operations') }}</span>
                </h1>

                <p class="text-xl md:text-2xl text-blue-100 mb-12 max-w-3xl mx-auto">
                    {{ __('A comprehensive ERP system designed to help you manage finances, clients, and operations with ease.') }}
                </p>

                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    @auth
                        <a href="{{ url('/dashboard') }}"
                           class="inline-flex items-center justify-center px-8 py-4 bg-white text-primary-600 font-semibold rounded-lg hover:bg-gray-50 transition shadow-xl text-lg">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                            {{ __('Go to Dashboard') }}
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                           class="inline-flex items-center justify-center px-8 py-4 bg-white text-primary-600 font-semibold rounded-lg hover:bg-gray-50 transition shadow-xl text-lg">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                            </svg>
                            {{ __('Get Started') }}
                        </a>

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}"
                               class="inline-flex items-center justify-center px-8 py-4 bg-transparent border-2 border-white text-white font-semibold rounded-lg hover:bg-white hover:text-primary-600 transition text-lg">
                                {{ __('Create Account') }}
                            </a>
                        @endif
                    @endauth
                </div>
            </div>
        </div>

        <!-- Features Section -->
        <div class="py-20 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-4xl font-bold text-slate-900 mb-4">
                        {{ __('Powerful Features') }}
                    </h2>
                    <p class="text-xl text-slate-600 max-w-2xl mx-auto">
                        {{ __('Everything you need to manage your business efficiently') }}
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <!-- Feature 1 -->
                    <div class="feature-card bg-white rounded-xl p-6 shadow-lg">
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-900 mb-2">{{ __('Financial Management') }}</h3>
                        <p class="text-slate-600 text-sm">{{ __('Track revenues, expenses, and manage financial documents efficiently.') }}</p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="feature-card bg-white rounded-xl p-6 shadow-lg">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-900 mb-2">{{ __('Client Management') }}</h3>
                        <p class="text-slate-600 text-sm">{{ __('Organize and manage your client relationships in one place.') }}</p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="feature-card bg-white rounded-xl p-6 shadow-lg">
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-900 mb-2">{{ __('Analytics & Reports') }}</h3>
                        <p class="text-slate-600 text-sm">{{ __('Gain insights with comprehensive financial analytics and reporting.') }}</p>
                    </div>

                    <!-- Feature 4 -->
                    <div class="feature-card bg-white rounded-xl p-6 shadow-lg">
                        <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-900 mb-2">{{ __('Cloud Storage') }}</h3>
                        <p class="text-slate-600 text-sm">{{ __('Securely store and access your documents from anywhere.') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Benefits Section -->
        <div class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                    <div>
                        <h2 class="text-4xl font-bold text-slate-900 mb-6">
                            {{ __('Why Choose Our ERP System?') }}
                        </h2>
                        <div class="space-y-6">
                            <div class="flex gap-4">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-slate-900 mb-1">{{ __('Easy to Use') }}</h3>
                                    <p class="text-slate-600">{{ __('Intuitive interface designed for efficiency and ease of use.') }}</p>
                                </div>
                            </div>

                            <div class="flex gap-4">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-slate-900 mb-1">{{ __('Secure & Reliable') }}</h3>
                                    <p class="text-slate-600">{{ __('Enterprise-grade security to keep your business data safe.') }}</p>
                                </div>
                            </div>

                            <div class="flex gap-4">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-slate-900 mb-1">{{ __('Multi-Currency Support') }}</h3>
                                    <p class="text-slate-600">{{ __('Handle transactions in multiple currencies with automatic conversions.') }}</p>
                                </div>
                            </div>

                            <div class="flex gap-4">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-slate-900 mb-1">{{ __('Real-Time Updates') }}</h3>
                                    <p class="text-slate-600">{{ __('Stay informed with instant notifications and real-time data sync.') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="relative">
                        <div class="aspect-square rounded-2xl bg-gradient-to-br from-primary-600 to-blue-900 shadow-2xl flex items-center justify-center">
                            <svg class="w-64 h-64 text-white opacity-20" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="bg-slate-900 text-slate-300 py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
                    <div>
                        <div class="flex items-center gap-2 mb-4">
                            @if(isset($globalAppSettings['app_logo']) && $globalAppSettings['app_logo'])
                                <img src="{{ asset('storage/' . $globalAppSettings['app_logo']) }}"
                                     alt="{{ $globalAppSettings['app_name'] ?? 'Logo' }}"
                                     class="h-8 w-auto">
                            @endif
                            <span class="text-lg font-bold text-white">
                                {{ $globalAppSettings['app_name'] ?? config('app.name', 'Laravel') }}
                            </span>
                        </div>
                        <p class="text-sm">{{ __('Modern ERP solution for growing businesses.') }}</p>
                    </div>

                    <div>
                        <h3 class="text-white font-semibold mb-4">{{ __('Quick Links') }}</h3>
                        <ul class="space-y-2 text-sm">
                            @auth
                                <li><a href="{{ url('/dashboard') }}" class="hover:text-white transition">{{ __('Dashboard') }}</a></li>
                            @else
                                <li><a href="{{ route('login') }}" class="hover:text-white transition">{{ __('Log in') }}</a></li>
                                @if (Route::has('register'))
                                    <li><a href="{{ route('register') }}" class="hover:text-white transition">{{ __('Sign up') }}</a></li>
                                @endif
                            @endauth
                        </ul>
                    </div>

                    <div>
                        <h3 class="text-white font-semibold mb-4">{{ __('Support') }}</h3>
                        <p class="text-sm">{{ __('Need help? Contact our support team.') }}</p>
                    </div>
                </div>

                <div class="border-t border-slate-700 pt-8 text-center text-sm">
                    <p>&copy; {{ date('Y') }} {{ $globalAppSettings['app_name'] ?? config('app.name') }}. {{ __('All rights reserved.') }}</p>
                </div>
            </div>
        </footer>
    </body>
</html>
