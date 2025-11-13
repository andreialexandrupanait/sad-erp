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

        <!-- Tailwind CSS CDN with custom config -->
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
        </style>

        <!-- Alpine.js for interactivity -->
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

        <!-- Alpine Collapse Plugin -->
        <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>

        <!-- Custom Styles for smooth transitions -->
        <style>
            * {
                scroll-behavior: smooth;
            }

            /* Custom scrollbar */
            ::-webkit-scrollbar {
                width: 8px;
                height: 8px;
            }

            ::-webkit-scrollbar-track {
                background: #f1f5f9;
            }

            ::-webkit-scrollbar-thumb {
                background: #cbd5e1;
                border-radius: 4px;
            }

            ::-webkit-scrollbar-thumb:hover {
                background: #94a3b8;
            }
        </style>
    </head>
    <body class="font-sans antialiased bg-slate-50" x-data="{
        sidebarOpen: true,
        touchStartX: 0,
        touchCurrentX: 0,
        isDragging: false
    }">
        <div class="flex h-screen overflow-hidden">
            <!-- Sidebar - toggleable on mobile, always visible on desktop -->
            <x-sidebar
                class="md:flex"
                x-show="sidebarOpen"
                @touchstart="if (window.innerWidth < 768) {
                    touchStartX = $event.touches[0].clientX;
                    isDragging = true;
                }"
                @touchmove="if (isDragging && window.innerWidth < 768) {
                    touchCurrentX = $event.touches[0].clientX;
                    const diff = touchCurrentX - touchStartX;
                    if (diff < 0) {
                        $el.style.transition = 'none';
                        $el.style.transform = 'translateX(' + diff + 'px)';
                    }
                }"
                @touchend="if (isDragging && window.innerWidth < 768) {
                    const diff = touchCurrentX - touchStartX;
                    isDragging = false;
                    $el.style.transition = 'transform 150ms ease-out';
                    $el.style.transform = '';
                    if (diff < -80) {
                        setTimeout(function() { sidebarOpen = false; }, 150);
                    }
                    touchStartX = 0;
                    touchCurrentX = 0;
                }"
                x-transition:enter="transition-transform ease-out duration-200 md:transition-none"
                x-transition:enter-start="-translate-x-full md:translate-x-0"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transition-transform ease-in duration-150 md:transition-none"
                x-transition:leave-start="translate-x-0 md:translate-x-0"
                x-transition:leave-end="-translate-x-full md:translate-x-0" />

            <!-- Overlay for mobile -->
            <div
                x-show="sidebarOpen"
                @click="sidebarOpen = false"
                x-transition:enter="transition-opacity ease-linear duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition-opacity ease-linear duration-300"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-slate-900/50 z-40 md:hidden"
                style="display: none;"
            ></div>

            <!-- Main Content Area -->
            <div class="flex-1 flex flex-col overflow-hidden">
                <!-- Global Header -->
                <header class="bg-white border-b border-slate-200 sticky top-0 z-30 flex-shrink-0 h-16">
                    <div class="flex items-center justify-between h-full px-4 md:px-6 gap-4">
                        <!-- Left: Toggle + Breadcrumb/Title -->
                        <div class="flex items-center gap-3 min-w-0 flex-1">
                            <!-- Sidebar Toggle -->
                            <button
                                @click="sidebarOpen = !sidebarOpen"
                                class="p-2 rounded-lg hover:bg-slate-100 transition-colors flex-shrink-0"
                                aria-label="Toggle sidebar"
                            >
                                <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                                </svg>
                            </button>

                            <!-- Page Title + Breadcrumb -->
                            <div class="min-w-0 flex-1">
                                @isset($pageTitle)
                                    <div class="flex flex-col">
                                        <h1 class="text-sm font-bold text-slate-900 truncate uppercase tracking-wide">{{ $pageTitle }}</h1>
                                        @if(!isset($hideBreadcrumb) || !$hideBreadcrumb)
                                            @isset($breadcrumb)
                                                <div class="mt-0.5">
                                                    {{ $breadcrumb }}
                                                </div>
                                            @else
                                                <div class="mt-0.5">
                                                    <x-breadcrumb />
                                                </div>
                                            @endisset
                                        @endif
                                    </div>
                                @else
                                    @isset($breadcrumb)
                                        {{ $breadcrumb }}
                                    @else
                                        @isset($header)
                                            {{ $header }}
                                        @else
                                            <x-breadcrumb />
                                        @endisset
                                    @endisset
                                @endisset
                            </div>
                        </div>

                        <!-- Right: Action Buttons -->
                        @isset($headerActions)
                            <div class="flex-shrink-0">
                                {{ $headerActions }}
                            </div>
                        @endisset
                    </div>
                </header>

                <!-- Page Content -->
                <main class="flex-1 overflow-y-auto bg-slate-50">
                    {{ $slot }}
                </main>
            </div>
        </div>

        <!-- Toast Notifications -->
        <x-toast />

        <!-- Scripts Stack -->
        @stack('scripts')
    </body>
</html>
