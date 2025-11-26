{{--
    Unsaved Form Warning Component

    Usage: Add this component inside any form that uses x-data
    <x-unsaved-form-warning />

    The form must have x-data with a formDirty property tracking changes.
    Or use the standalone version with data attributes on the form.
--}}

<div x-data="unsavedFormWarning()" x-init="init()">
    {{-- Warning banner shown when form has unsaved changes --}}
    <div x-show="isDirty"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 transform -translate-y-2"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed top-16 left-1/2 transform -translate-x-1/2 z-50 bg-amber-50 border border-amber-200 rounded-lg shadow-lg px-4 py-2 flex items-center gap-2"
         style="display: none;">
        <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <span class="text-sm font-medium text-amber-700">{{ __('Ai modificări nesalvate') }}</span>
    </div>
</div>

<script>
function unsavedFormWarning() {
    return {
        isDirty: false,
        originalData: {},
        form: null,

        init() {
            // Find the closest form element
            this.form = this.$el.closest('form');
            if (!this.form) return;

            // Store original values
            this.storeOriginalData();

            // Listen for input changes
            this.form.addEventListener('input', () => this.checkDirty());
            this.form.addEventListener('change', () => this.checkDirty());

            // Warn before leaving page
            window.addEventListener('beforeunload', (e) => {
                if (this.isDirty) {
                    e.preventDefault();
                    e.returnValue = '';
                }
            });

            // Don't warn when form is submitted
            this.form.addEventListener('submit', () => {
                this.isDirty = false;
            });
        },

        storeOriginalData() {
            const formData = new FormData(this.form);
            for (let [key, value] of formData.entries()) {
                this.originalData[key] = value;
            }
        },

        checkDirty() {
            const formData = new FormData(this.form);
            let dirty = false;

            for (let [key, value] of formData.entries()) {
                // Skip file inputs and csrf token
                if (key === '_token' || key.includes('files')) continue;

                if (this.originalData[key] !== value) {
                    dirty = true;
                    break;
                }
            }

            this.isDirty = dirty;
        }
    };
}
</script>
