@props([
    'id',
    'title',
    'event',
    'route',
    'prefix',
    'firstField',
    'buttonText',
])

<div x-data="quickAddSlideOver('{{ $id }}', '{{ $route }}', '{{ $prefix }}', '{{ $firstField }}')"
     x-on:{{ $event }}.window="open()">

    <div x-show="isOpen"
         x-cloak
         class="fixed inset-0 z-[100] overflow-hidden"
         :aria-labelledby="'slide-over-' + id + '-title'"
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
                            <h2 class="text-lg font-semibold text-slate-900" :id="'slide-over-' + id + '-title'">
                                {{ $title }}
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
                    </div>

                    {{-- Form --}}
                    <form @submit.prevent="submit()" class="flex-1 flex flex-col">
                        <div class="flex-1 px-4 py-6 sm:px-6 space-y-6">
                            {{ $slot }}
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
                                    <span x-text="saving ? '{{ __('Creating...') }}' : '{{ $buttonText }}'"></span>
                                </x-ui.button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@once
@push('scripts')
<script>
function quickAddSlideOver(id, route, prefix, firstField) {
    return {
        id: id,
        route: route,
        prefix: prefix,
        firstField: firstField,
        isOpen: false,
        saving: false,

        open() {
            this.isOpen = true;
            this.$nextTick(() => {
                const input = document.querySelector(`[name="${this.prefix}${this.firstField}"]`);
                if (input) input.focus();
            });
        },

        close() {
            this.isOpen = false;
            this.resetForm();
        },

        resetForm() {
            const form = this.$el.querySelector('form');
            if (form) {
                form.querySelectorAll('input:not([type="hidden"]):not([type="checkbox"]), select, textarea').forEach(el => {
                    if (el.name && el.name.startsWith(this.prefix)) {
                        el.value = '';
                        el.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                });
                form.querySelectorAll('input[type="checkbox"]').forEach(el => {
                    if (el.name && el.name.startsWith(this.prefix)) {
                        el.checked = false;
                    }
                });
            }
        },

        async submit() {
            this.saving = true;
            const formData = this.collectFormData();

            try {
                const response = await fetch(this.route, {
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
                    if (data.errors) {
                        const firstError = Object.values(data.errors)[0];
                        showToast(Array.isArray(firstError) ? firstError[0] : firstError, 'error');
                    } else if (data.message) {
                        showToast(data.message, 'error');
                    }
                    return;
                }

                showToast(data.message || '{{ __("Created successfully") }}');
                this.close();

                // Dispatch event so other components can react
                window.dispatchEvent(new CustomEvent('quick-add-created', {
                    detail: { type: this.id, data: data }
                }));

            } catch (error) {
                console.error('Error creating item:', error);
                showToast('{{ __("An error occurred. Please try again.") }}', 'error');
            } finally {
                this.saving = false;
            }
        },

        collectFormData() {
            const data = {};
            const form = this.$el.querySelector('form');
            if (!form) return data;

            form.querySelectorAll('input, select, textarea').forEach(el => {
                if (el.name && el.name.startsWith(this.prefix)) {
                    const key = el.name.replace(this.prefix, '');
                    if (el.type === 'checkbox') {
                        data[key] = el.checked ? 1 : 0;
                    } else if (el.value !== '') {
                        data[key] = el.value;
                    }
                }
            });

            return data;
        }
    };
}
</script>
@endpush
@endonce
