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
            .focus\:ring-primary-500:focus { --tw-ring-color: var(--primary-color) !important; }

            /* Gradient background */
            .gradient-bg {
                background: linear-gradient(135deg, var(--primary-color) 0%, #1e3a8a 100%);
            }

            /* Glass morphism effect */
            .glass {
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(10px);
            }

        </style>
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen gradient-bg flex items-center justify-center p-4">
            <!-- Background Pattern -->
            <div class="absolute inset-0 opacity-10">
                <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'1\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
            </div>

            <!-- Main Content -->
            <div class="relative w-full max-w-md">
                <!-- Auth Card with integrated logo -->
                <div class="glass shadow-2xl rounded-2xl p-8">
                    <!-- Logo inside card -->
                    <div class="text-center mb-6">
                        @if(isset($globalAppSettings['app_logo']) && $globalAppSettings['app_logo'])
                            <img src="{{ asset('storage/' . $globalAppSettings['app_logo']) }}"
                                 alt="{{ $globalAppSettings['app_name'] ?? 'Logo' }}"
                                 class="h-12 w-auto mx-auto">
                        @else
                            <svg class="w-12 h-12 mx-auto text-primary-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5zm0 2.18l8 4.09v8.55c0 4.35-2.98 8.41-7.5 9.88-.68-.22-1.37-.5-2.04-.85C7.12 24.39 4 20.48 4 15.82V8.27l8-4.09z"/>
                            </svg>
                        @endif
                    </div>
                    <div class="border-t border-slate-200 mb-6"></div>
                    {{ $slot }}
                </div>

                <!-- Footer -->
                <div class="text-center mt-6">
                    <p class="text-blue-100 text-sm">
                        &copy; {{ date('Y') }} {{ $globalAppSettings['app_name'] ?? config('app.name') }}. {{ __('All rights reserved.') }}
                    </p>
                </div>
            </div>
        </div>
    </body>
</html>
