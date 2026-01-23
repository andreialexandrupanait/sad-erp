<x-app-layout>
    <x-slot name="pageTitle">{{ __('Credentials') }}</x-slot>

    <x-slot name="headerActions">
        <a href="{{ route('credentials.create') }}" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 bg-slate-900 text-slate-50 hover:bg-slate-900/90 shadow h-10 px-4 py-2">
            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            {{ __('New Credential') }}
        </a>
    </x-slot>

    <div class="p-4 md:p-6 space-y-4 md:space-y-6" x-data="{
        ...credentialsSearch(),
        ...bulkSelection({
            idAttribute: 'data-credential-id',
            rowSelector: '[data-selectable]'
        })
    }">
        <!-- Success Messages -->
        @if (session('success'))
            <x-ui.alert variant="success">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>{{ session('success') }}</div>
            </x-ui.alert>
        @endif

        <!-- Search and Filter Form -->
        <x-ui.card>
            <x-ui.card-content>
                <form method="GET" action="{{ route('credentials.index') }}" x-ref="filterForm">
                    <div class="flex flex-col lg:flex-row gap-3 md:gap-4">
                        <!-- Search -->
                        <div class="flex-1">
                            <x-ui.input
                                type="text"
                                name="search"
                                id="search"
                                x-model="search"
                                @input.debounce.400ms="performSearch"
                                value="{{ request('search') }}"
                                placeholder="{{ __('Search credentials, sites...') }}"
                            />
                        </div>

                        <!-- Client Filter -->
                        <div class="w-full lg:w-64">
                            <x-ui.searchable-select
                                name="client_id"
                                :options="$clients"
                                :selected="request('client_id')"
                                :placeholder="__('All Clients')"
                                :emptyLabel="__('All Clients')"
                                onchange="this.form.submit()"
                            />
                        </div>

                        <!-- Buttons -->
                        <div class="flex items-end gap-2">
                            <x-ui.button type="submit" variant="default">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                {{ __('Search') }}
                            </x-ui.button>
                            @if(request()->has('search') || request()->has('client_id'))
                                <x-ui.button type="button" variant="outline" @click="clearFilters">
                                    {{ __('Clear') }}
                                </x-ui.button>
                            @endif
                        </div>
                    </div>
                </form>
            </x-ui.card-content>
        </x-ui.card>

        <!-- Bulk Actions Toolbar -->
        <x-bulk-toolbar resource="credentials">
            <x-ui.button variant="outline" class="!bg-slate-800 !border-slate-600 !text-white hover:!bg-slate-700"
                @click="performBulkAction('export', '{{ route('credentials.bulk-export') }}', {
                    title: '{{ __('Export Credentials') }}',
                    message: '{{ __('Export selected credentials to CSV?') }}'
                })">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                {{ __('Export CSV') }}
            </x-ui.button>

            <x-ui.button variant="destructive"
                @click="performBulkAction('delete', '{{ route('credentials.bulk-update') }}', {
                    title: '{{ __('Delete Credentials') }}',
                    message: '{{ __('Are you sure you want to delete the selected credentials? This cannot be undone.') }}'
                })">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                {{ __('Delete Selected') }}
            </x-ui.button>
        </x-bulk-toolbar>

        <!-- Credentials Cards -->
        <div id="credentials-container">
            @include('credentials.partials.credentials-list')
        </div>
    </div>

    <!-- Toast Notifications -->
    <x-toast />

    <!-- Email Credentials Modal -->
    <div x-data="emailCredentialsModal()"
         x-show="isOpen"
         x-cloak
         @open-email-modal.window="open($event.detail.siteName, $event.detail.clientEmail, $event.detail.clientName)"
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="modal-title"
         role="dialog"
         aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Backdrop -->
            <div x-show="isOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                 @click="close()"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <!-- Modal Panel -->
            <div x-show="isOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">

                <div class="absolute top-0 right-0 pt-4 pr-4">
                    <button type="button" @click="close()" class="rounded-md text-slate-400 hover:text-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-500">
                        <span class="sr-only">{{ __('Close') }}</span>
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="sm:flex sm:items-start mb-6">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-10 w-10 rounded-full bg-slate-100 sm:mx-0">
                        <svg class="h-5 w-5 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                        <h3 class="text-lg font-semibold text-slate-900" id="modal-title">
                            {{ __('Send Credentials via Email') }}
                        </h3>
                        <p class="mt-1 text-sm text-slate-500" x-text="'{{ __('Send credentials for') }} ' + siteName"></p>
                    </div>
                </div>

                <form @submit.prevent="sendEmail()" class="space-y-4">
                    <!-- Email Address -->
                    <div class="space-y-2">
                        <x-ui.label>{{ __('Email address') }}</x-ui.label>

                        <!-- When client has email - show both options -->
                        <template x-if="clientEmail">
                            <div class="space-y-2">
                                <label class="flex items-center p-3 border rounded-md cursor-pointer transition-colors"
                                       :class="emailType === 'client' ? 'border-slate-900 bg-slate-50' : 'border-slate-200 hover:border-slate-300'">
                                    <input type="radio" name="email_type" value="client"
                                           x-model="emailType"
                                           class="h-4 w-4 text-slate-900 border-slate-300 focus:ring-slate-500">
                                    <div class="ml-3">
                                        <span class="block text-sm font-medium text-slate-900" x-text="clientEmail"></span>
                                        <span class="block text-xs text-slate-500" x-text="clientName ? clientName + ' - {{ __('Client') }}' : '{{ __('Client email') }}'"></span>
                                    </div>
                                </label>

                                <label class="flex items-center p-3 border rounded-md cursor-pointer transition-colors"
                                       :class="emailType === 'custom' ? 'border-slate-900 bg-slate-50' : 'border-slate-200 hover:border-slate-300'">
                                    <input type="radio" name="email_type" value="custom"
                                           x-model="emailType"
                                           class="h-4 w-4 text-slate-900 border-slate-300 focus:ring-slate-500">
                                    <span class="ml-3 text-sm text-slate-700">{{ __('Other email address') }}</span>
                                </label>

                                <input x-show="emailType === 'custom'"
                                       x-transition
                                       type="email"
                                       x-model="customEmail"
                                       class="flex h-10 w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm ring-offset-white placeholder:text-slate-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-950 focus-visible:ring-offset-2"
                                       placeholder="{{ __('Enter email address') }}"
                                       :required="emailType === 'custom'">
                            </div>
                        </template>

                        <!-- When no client email - show just input field -->
                        <template x-if="!clientEmail">
                            <input type="email"
                                   x-model="customEmail"
                                   class="flex h-10 w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm ring-offset-white placeholder:text-slate-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-950 focus-visible:ring-offset-2"
                                   placeholder="{{ __('Enter email address') }}"
                                   required>
                        </template>
                    </div>

                    <!-- Subject -->
                    <div class="space-y-2">
                        <x-ui.label for="email_subject">{{ __('Subject') }}</x-ui.label>
                        <input type="text"
                               id="email_subject"
                               x-model="subject"
                               class="flex h-10 w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm ring-offset-white placeholder:text-slate-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-950 focus-visible:ring-offset-2">
                    </div>

                    <!-- Message -->
                    <div class="space-y-2">
                        <x-ui.label for="email_message">
                            {{ __('Message') }}
                            <span class="font-normal text-slate-400">({{ __('optional') }})</span>
                        </x-ui.label>
                        <textarea id="email_message"
                                  x-model="message"
                                  rows="3"
                                  class="flex min-h-[80px] w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm ring-offset-white placeholder:text-slate-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-950 focus-visible:ring-offset-2 resize-y"
                                  placeholder="{{ __('Add a personal message...') }}"></textarea>
                    </div>

                    <!-- Error Message -->
                    <div x-show="error" x-transition class="rounded-md bg-red-50 border border-red-200 p-3">
                        <div class="flex items-center gap-2">
                            <svg class="h-4 w-4 text-red-500 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                            <p class="text-sm text-red-700" x-text="error"></p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                        <button type="button"
                                @click="close()"
                                class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium h-10 px-4 py-2 border border-slate-200 bg-white text-slate-900 hover:bg-slate-100 shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-950 focus-visible:ring-offset-2">
                            {{ __('Cancel') }}
                        </button>
                        <button type="submit"
                                :disabled="sending"
                                class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium h-10 px-4 py-2 bg-slate-900 text-slate-50 hover:bg-slate-900/90 shadow transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-950 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50">
                            <svg x-show="sending" class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span x-text="sending ? '{{ __('Sending...') }}' : '{{ __('Send Email') }}'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    // Email Modal
    function emailCredentialsModal() {
        return {
            isOpen: false,
            siteName: '',
            clientEmail: '',
            clientName: '',
            emailType: 'client',
            customEmail: '',
            subject: '',
            message: '',
            sending: false,
            error: '',

            open(siteName, clientEmail, clientName) {
                this.siteName = siteName;
                this.clientEmail = clientEmail || '';
                this.clientName = clientName || '';
                this.emailType = clientEmail ? 'client' : 'custom';
                this.customEmail = '';
                this.subject = '{{ __('Access Credentials for') }} ' + siteName;
                this.message = '';
                this.error = '';
                this.sending = false;
                this.isOpen = true;
            },

            close() {
                this.isOpen = false;
            },

            async sendEmail() {
                const email = this.emailType === 'client' ? this.clientEmail : this.customEmail;

                if (!email) {
                    this.error = '{{ __('Please enter an email address.') }}';
                    return;
                }

                this.sending = true;
                this.error = '';

                try {
                    const response = await fetch(`/credentials/email-site/${encodeURIComponent(this.siteName)}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            email: email,
                            subject: this.subject,
                            message: this.message
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        this.close();
                        showToast(data.message, 'success');
                    } else {
                        this.error = data.message || '{{ __('Failed to send email.') }}';
                    }
                } catch (err) {
                    this.error = '{{ __('An error occurred. Please try again.') }}';
                } finally {
                    this.sending = false;
                }
            }
        };
    }

    // Password reveal component with auto-hide
    function passwordReveal(credentialId, hasPassword) {
        return {
            credentialId,
            hasPassword,
            password: '',
            visible: false,
            loading: false,
            autoHideSeconds: 0,
            autoHideTimer: null,
            countdownTimer: null,

            async toggle() {
                if (this.visible) {
                    this.hide();
                } else {
                    await this.show();
                }
            },

            async show() {
                if (!this.hasPassword) return;

                // If password already loaded, just show it
                if (this.password) {
                    this.visible = true;
                    this.startAutoHide();
                    return;
                }

                // Fetch password from server
                this.loading = true;
                try {
                    const response = await fetch(`/credentials/${this.credentialId}/password`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                        },
                        credentials: 'same-origin'
                    });

                    if (!response.ok) {
                        throw new Error('Failed to fetch password');
                    }

                    const data = await response.json();
                    this.password = data.password || '';
                    this.visible = true;
                    this.startAutoHide();
                } catch (error) {
                    showToast('{{ __("Failed to load password") }}', 'error');
                } finally {
                    this.loading = false;
                }
            },

            hide() {
                this.visible = false;
                this.clearTimers();
            },

            startAutoHide() {
                this.clearTimers();
                this.autoHideSeconds = 15;

                // Countdown timer
                this.countdownTimer = setInterval(() => {
                    this.autoHideSeconds--;
                    if (this.autoHideSeconds <= 0) {
                        this.hide();
                    }
                }, 1000);
            },

            clearTimers() {
                if (this.autoHideTimer) {
                    clearTimeout(this.autoHideTimer);
                    this.autoHideTimer = null;
                }
                if (this.countdownTimer) {
                    clearInterval(this.countdownTimer);
                    this.countdownTimer = null;
                }
                this.autoHideSeconds = 0;
            },

            copyPassword() {
                if (this.password) {
                    navigator.clipboard.writeText(this.password).then(() => {
                        showToast('{{ __("Password copied to clipboard") }}');
                        // Reset auto-hide timer after copy
                        this.startAutoHide();
                    });
                }
            }
        };
    }

    function copyToClipboard(text, element) {
        navigator.clipboard.writeText(text).then(() => {
            showToast('{{ __("Copied to clipboard") }}');
        });
    }

    function showToast(message, type = 'success') {
        window.dispatchEvent(new CustomEvent('toast', {
            detail: { message, type }
        }));
    }

    function credentialsSearch() {
        return {
            search: '{{ request('search', '') }}',
            clientId: '{{ request('client_id', '') }}',
            loading: false,
            searchTimeout: null,

            init() {
                // Restore filters from localStorage if no URL params
                const savedFilters = localStorage.getItem('credentialsFilters');
                if (savedFilters && !window.location.search) {
                    const filters = JSON.parse(savedFilters);
                    if (filters.search || filters.clientId) {
                        localStorage.removeItem('credentialsFilters');
                        // Redirect with saved filters
                        const url = new URL(window.location.origin + '{{ route('credentials.index') }}');
                        if (filters.search) url.searchParams.set('search', filters.search);
                        if (filters.clientId) url.searchParams.set('client_id', filters.clientId);
                        window.location.href = url.toString();
                        return;
                    }
                }
                // Clear saved filters after restoring
                localStorage.removeItem('credentialsFilters');
            },

            performSearch() {
                // Debounce and navigate
                clearTimeout(this.searchTimeout);
                this.searchTimeout = setTimeout(() => {
                    this.navigateWithFilters();
                }, 300);
            },

            navigateWithFilters() {
                this.loading = true;
                const url = new URL(window.location.origin + '{{ route('credentials.index') }}');
                if (this.search) url.searchParams.set('search', this.search);
                if (this.clientId) url.searchParams.set('client_id', this.clientId);
                window.location.href = url.toString();
            },

            clearFilters() {
                this.search = '';
                this.clientId = '';
                localStorage.removeItem('credentialsFilters');
                window.location.href = '{{ route('credentials.index') }}';
            },

            saveFilters() {
                // Save current filters to localStorage before navigating away
                if (this.search || this.clientId) {
                    localStorage.setItem('credentialsFilters', JSON.stringify({
                        search: this.search,
                        clientId: this.clientId
                    }));
                }
            }
        };
    }
    </script>

    {{-- Quick Add Credential Slide-Over --}}
    <div x-data="quickAddCredential()"
         @open-quick-add-credential.window="open()"
         @open-quick-add-credential-for-site.window="openForSite($event.detail.siteName, $event.detail.clientId)"
         @credential-created.window="onCredentialCreated($event.detail)">

        {{-- Slide-Over Panel --}}
        <div x-show="isOpen"
             x-cloak
             class="fixed inset-0 z-[100] overflow-hidden"
             aria-labelledby="slide-over-title"
             role="dialog"
             aria-modal="true">

            {{-- Backdrop --}}
            <div x-show="isOpen"
                 x-transition:enter="ease-in-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in-out duration-300"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity"
                 @click="close()"></div>

            {{-- Panel --}}
            <div class="fixed inset-y-0 right-0 flex max-w-full pl-10">
                <div x-show="isOpen"
                     x-transition:enter="transform transition ease-in-out duration-300"
                     x-transition:enter-start="translate-x-full"
                     x-transition:enter-end="translate-x-0"
                     x-transition:leave="transform transition ease-in-out duration-300"
                     x-transition:leave-start="translate-x-0"
                     x-transition:leave-end="translate-x-full"
                     class="w-screen max-w-lg"
                     @keydown.escape.window="close()">

                    <div class="flex h-full flex-col overflow-y-auto bg-white shadow-xl">
                        {{-- Header --}}
                        <div class="bg-slate-50 px-4 py-6 sm:px-6 border-b">
                            <div class="flex items-center justify-between">
                                <h2 class="text-lg font-semibold text-slate-900" id="slide-over-title">
                                    {{ __('Quick Add Credential') }}
                                </h2>
                                <button type="button"
                                        @click="close()"
                                        class="rounded-md text-slate-400 hover:text-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <span class="sr-only">{{ __('Close') }}</span>
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                            <p class="mt-1 text-sm text-slate-500" x-show="prefilledSite" x-cloak>
                                {{ __('Adding to site:') }} <span class="font-medium text-slate-700" x-text="prefilledSite"></span>
                            </p>
                        </div>

                        {{-- Form --}}
                        <form @submit.prevent="submit()" class="flex-1 flex flex-col">
                            <div class="flex-1 px-4 py-6 sm:px-6 space-y-6">
                                <x-credential-form-fields
                                    :clients="$clients"
                                    :platforms="$platforms"
                                    :sites="$sites"
                                    :clientStatuses="$clientStatuses"
                                    prefix="quick_"
                                    :compact="true"
                                />
                            </div>

                            {{-- Footer --}}
                            <div class="flex-shrink-0 border-t border-slate-200 px-4 py-4 sm:px-6">
                                <div class="flex justify-end gap-3">
                                    <x-ui.button type="button" variant="ghost" @click="close()">
                                        {{ __('Cancel') }}
                                    </x-ui.button>
                                    <x-ui.button type="submit" variant="default" x-bind:disabled="saving">
                                        <svg x-show="saving" class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span x-text="saving ? '{{ __('Creating...') }}' : '{{ __('Create Credential') }}'"></span>
                                    </x-ui.button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function quickAddCredential() {
        return {
            isOpen: false,
            saving: false,
            prefilledSite: '',
            prefilledClientId: '',

            open() {
                this.prefilledSite = '';
                this.prefilledClientId = '';
                this.isOpen = true;
                this.$nextTick(() => {
                    // Focus first input
                    const firstInput = document.querySelector('[name="quick_site_name"]');
                    if (firstInput) firstInput.focus();
                });
            },

            openForSite(siteName, clientId) {
                this.prefilledSite = siteName || '';
                this.prefilledClientId = clientId || '';
                this.isOpen = true;
                this.$nextTick(() => {
                    // Pre-fill site name
                    const siteInput = document.querySelector('[name="quick_site_name"]');
                    if (siteInput && siteName) {
                        siteInput.value = siteName;
                        siteInput.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                    // Pre-fill client_id if provided
                    if (clientId) {
                        const clientSelect = document.querySelector('[name="quick_client_id"]');
                        if (clientSelect) {
                            clientSelect.value = clientId;
                            clientSelect.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                    }
                    // Focus platform select since site and client are prefilled
                    const platformSelect = document.querySelector('[name="quick_platform"]');
                    if (platformSelect) {
                        platformSelect.focus();
                    } else {
                        // Fallback to username
                        const usernameInput = document.querySelector('[name="quick_username"]');
                        if (usernameInput) usernameInput.focus();
                    }
                });
            },

            close() {
                this.isOpen = false;
                this.resetForm();
            },

            resetForm() {
                // Clear form fields
                const form = document.querySelector('[x-data="quickAddCredential()"] form');
                if (form) {
                    form.querySelectorAll('input:not([type="hidden"]), select, textarea').forEach(el => {
                        if (el.name && el.name.startsWith('quick_')) {
                            el.value = '';
                            el.dispatchEvent(new Event('input', { bubbles: true }));
                        }
                    });
                }
            },

            async submit() {
                this.saving = true;

                // Collect form data
                const formData = this.collectFormData();

                try {
                    const response = await fetch('{{ route('credentials.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify(formData)
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        // Show validation errors
                        if (data.errors) {
                            // Dispatch errors to form fields component
                            const formFieldsEl = document.querySelector('[x-data*="credentialFormFields"]');
                            if (formFieldsEl && formFieldsEl.__x) {
                                formFieldsEl.__x.$data.errors = data.errors;
                            }
                            showToast(data.message || '{{ __("Please fix the errors below") }}', 'error');
                        }
                        return;
                    }

                    // Success
                    showToast(data.message || '{{ __("Credential created successfully") }}');
                    this.close();

                    // Dispatch event for any listeners
                    window.dispatchEvent(new CustomEvent('credential-created', { detail: data }));

                    // Reload the page to show new credential
                    window.location.reload();

                } catch (error) {
                    showToast('{{ __("An error occurred. Please try again.") }}', 'error');
                } finally {
                    this.saving = false;
                }
            },

            collectFormData() {
                const data = {};
                const form = document.querySelector('[x-data="quickAddCredential()"] form');
                if (!form) return data;

                form.querySelectorAll('input, select, textarea').forEach(el => {
                    if (el.name && el.name.startsWith('quick_')) {
                        const key = el.name.replace('quick_', '');
                        if (el.type === 'checkbox') {
                            data[key] = el.checked;
                        } else if (el.value) {
                            data[key] = el.value;
                        }
                    }
                });

                return data;
            },

            onCredentialCreated(detail) {
                // Can be used for additional actions after creation
            }
        };
    }
    </script>
</x-app-layout>
