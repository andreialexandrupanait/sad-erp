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

        <!-- Alpine Collapse Plugin (must load before Alpine.js) -->
        <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>

        <!-- Alpine.js for interactivity -->
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

        <!-- Choices.js for searchable dropdowns -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js@10.2.0/public/assets/styles/choices.min.css">
        <script src="https://cdn.jsdelivr.net/npm/choices.js@10.2.0/public/assets/scripts/choices.min.js"></script>

        <!-- Task View CSS - ClickUp-style horizontal scroll -->
        <link rel="stylesheet" href="{{ asset('css/task-view.css') }}">

        <!-- Alpine.js x-cloak - Hide elements until Alpine is ready -->
        <style>
            [x-cloak] {
                display: none !important;
            }
        </style>

        <!-- Custom Styles for smooth transitions -->
        <style>
            /* Choices.js custom styling to match Tailwind design */
            .choices {
                margin-bottom: 0;
            }

            .choices__inner {
                background: white;
                border: 1px solid #e2e8f0;
                border-radius: 0.5rem;
                padding: 0.5rem 0.75rem;
                font-size: 0.875rem;
                line-height: 1.5rem;
                box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
                min-height: 40px;
                height: 40px;
                display: flex;
                align-items: center;
            }

            .choices__inner:hover {
                border-color: #cbd5e1;
            }

            .choices.is-focused .choices__inner {
                border-color: var(--primary-color);
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            }

            .choices__list--dropdown {
                border: 1px solid #e2e8f0;
                border-radius: 0.5rem;
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
                z-index: 100;
                margin-top: 0.25rem;
            }

            .choices__list--dropdown .choices__list {
                max-height: 300px;
            }

            .choices__input {
                font-size: 0.875rem;
                padding: 0.625rem 0.875rem;
                border-radius: 0.5rem;
                margin: 0.5rem;
            }

            .choices__input:focus {
                outline: none;
            }

            .choices__list--dropdown .choices__item {
                padding: 0.625rem 0.875rem;
                font-size: 0.875rem;
                color: #334155;
            }

            .choices__list--dropdown .choices__item--selectable {
                padding-right: 0.875rem;
            }

            .choices__list--dropdown .choices__item--selectable.is-highlighted {
                background-color: #f8fafc;
                color: #0f172a;
            }

            .choices__list--dropdown .choices__item--selectable[aria-selected="true"] {
                background-color: #eff6ff;
                color: var(--primary-color);
                font-weight: 500;
            }

            .choices__item--selectable::after {
                display: none;
            }

            .choices[data-type*=select-one] .choices__button {
                background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2394a3b8'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M6 18L18 6M6 6l12 12'/%3E%3C/svg%3E");
                background-size: 12px;
                width: 12px;
                height: 12px;
                opacity: 1;
                padding: 0;
                border-left: none;
                margin-left: 8px;
                margin-right: 0;
            }

            .choices__placeholder {
                opacity: 0.6;
                color: #94a3b8;
            }

            /* Optgroup (parent category) styling */
            .choices__group {
                padding: 0.5rem 0.875rem;
                font-weight: 600;
                font-size: 0.75rem;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                color: #475569;
                background-color: #f8fafc;
                border-top: 1px solid #e2e8f0;
            }

            .choices__group:first-child {
                border-top: none;
            }

            /* Child options (inside optgroup) - add indentation */
            .choices__list[role="listbox"] .choices__item[data-group-id] {
                padding-left: 2rem;
                font-size: 0.875rem;
            }

            /* Add a subtle indicator before child items */
            .choices__list[role="listbox"] .choices__item[data-group-id]::before {
                content: '└';
                position: absolute;
                left: 1rem;
                color: #cbd5e1;
            }
        </style>

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

        <!-- Global Choices.js Initialization -->
        <script>
        // Auto-initialize Choices.js on all select elements
        document.addEventListener('DOMContentLoaded', function() {
            // Find all select elements that aren't already initialized
            const selects = document.querySelectorAll('select:not(.choices__input)');

            selects.forEach(function(select) {
                // Skip if already initialized or if it's a native select we want to keep
                if (select.classList.contains('choices-initialized')) {
                    return;
                }

                // Check if select has multiple options (more than 5 = good candidate for search)
                const optionCount = select.options.length;

                // Initialize Choices.js on selects with many options or data attribute
                if (optionCount > 10 || select.hasAttribute('data-searchable')) {
                    try {
                        new Choices(select, {
                            searchEnabled: true,
                            searchPlaceholderValue: 'Caută...',
                            itemSelectText: '',
                            shouldSort: false,
                            removeItemButton: true,
                            noResultsText: 'Nu s-au găsit rezultate',
                            noChoicesText: 'Nu există opțiuni',
                            searchResultLimit: 100
                        });
                        select.classList.add('choices-initialized');
                    } catch (e) {
                        console.warn('Failed to initialize Choices.js on select:', e);
                    }
                }
            });
        });

        // Re-initialize on dynamic content (for modals, AJAX, etc.)
        document.addEventListener('alpine:initialized', function() {
            // Wait a bit for Alpine to finish rendering
            setTimeout(function() {
                const selects = document.querySelectorAll('select:not(.choices__input):not(.choices-initialized)');
                selects.forEach(function(select) {
                    const optionCount = select.options.length;
                    if (optionCount > 10 || select.hasAttribute('data-searchable')) {
                        try {
                            new Choices(select, {
                                searchEnabled: true,
                                searchPlaceholderValue: 'Caută...',
                                itemSelectText: '',
                                shouldSort: false,
                                removeItemButton: true,
                                noResultsText: 'Nu s-au găsit rezultate',
                                noChoicesText: 'Nu există opțiuni',
                                searchResultLimit: 100
                            });
                            select.classList.add('choices-initialized');
                        } catch (e) {
                            console.warn('Failed to initialize Choices.js:', e);
                        }
                    }
                });
            }, 100);
        });
        </script>

        <!-- Scripts Stack -->
        @stack('scripts')
    </body>
</html>
