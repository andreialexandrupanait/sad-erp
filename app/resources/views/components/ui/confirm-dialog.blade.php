@props([
    'id' => 'confirm-dialog',
    'title' => __('Confirm Action'),
    'message' => __('Are you sure you want to proceed?'),
    'confirmText' => __('Confirm'),
    'cancelText' => __('Cancel'),
    'variant' => 'destructive',
])

<div
    x-data="{
        open: false,
        title: '{{ $title }}',
        message: '{{ $message }}',
        confirmText: '{{ $confirmText }}',
        cancelText: '{{ $cancelText }}',
        onConfirm: null,
        loading: false,

        show(options = {}) {
            this.title = options.title || '{{ $title }}';
            this.message = options.message || '{{ $message }}';
            this.confirmText = options.confirmText || '{{ $confirmText }}';
            this.cancelText = options.cancelText || '{{ $cancelText }}';
            this.onConfirm = options.onConfirm || null;
            this.loading = false;
            this.open = true;
            this.$nextTick(() => {
                this.$refs.cancelBtn?.focus();
            });
        },

        async confirm() {
            if (this.onConfirm) {
                this.loading = true;
                try {
                    await this.onConfirm();
                } finally {
                    this.loading = false;
                    this.open = false;
                }
            } else {
                this.open = false;
            }
        },

        cancel() {
            this.open = false;
        }
    }"
    x-on:confirm-dialog.window="show($event.detail)"
    x-on:keydown.escape.window="cancel()"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto"
    aria-labelledby="{{ $id }}-title"
    aria-describedby="{{ $id }}-description"
    role="dialog"
    aria-modal="true"
>
    <!-- Backdrop -->
    <div
        x-show="open"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"
        @click="cancel()"
        aria-hidden="true"
    ></div>

    <!-- Dialog Panel -->
    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
        <div
            x-show="open"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg"
            @click.away="cancel()"
            x-trap.noscroll="open"
        >
            <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <!-- Icon -->
                    <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full {{ $variant === 'destructive' ? 'bg-red-100' : 'bg-amber-100' }} sm:mx-0 sm:h-10 sm:w-10" aria-hidden="true">
                        @if($variant === 'destructive')
                            <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                        @else
                            <svg class="h-6 w-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" />
                            </svg>
                        @endif
                    </div>

                    <!-- Content -->
                    <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                        <h3 id="{{ $id }}-title" class="text-lg font-semibold leading-6 text-slate-900" x-text="title"></h3>
                        <div class="mt-2">
                            <p id="{{ $id }}-description" class="text-sm text-slate-500" x-text="message"></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-slate-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 gap-3">
                <button
                    type="button"
                    @click="confirm()"
                    :disabled="loading"
                    :aria-busy="loading"
                    class="inline-flex w-full justify-center rounded-md px-4 py-2.5 text-sm font-semibold text-white shadow-sm sm:ml-3 sm:w-auto {{ $variant === 'destructive' ? 'bg-red-600 hover:bg-red-500' : 'bg-amber-600 hover:bg-amber-500' }} disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                    <template x-if="loading">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </template>
                    <span x-text="confirmText"></span>
                </button>
                <button
                    type="button"
                    x-ref="cancelBtn"
                    @click="cancel()"
                    :disabled="loading"
                    class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-4 py-2.5 text-sm font-semibold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    x-text="cancelText"
                ></button>
            </div>
        </div>
    </div>
</div>
