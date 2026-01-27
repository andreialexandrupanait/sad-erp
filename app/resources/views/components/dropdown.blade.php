@props(['align' => 'right', 'width' => '48', 'contentClasses' => 'py-1 bg-white'])

@php
$width = match ($width) {
    '48' => 'w-48',
    default => $width,
};
@endphp

<div class="relative"
     x-data="dropdown({ align: '{{ $align }}' })"
     @click.outside="close()"
     @close.stop="close()">
    <div x-ref="trigger"
         @click="toggle()"
         :aria-expanded="open.toString()"
         aria-haspopup="true">
        {{ $trigger }}
    </div>

    <div x-ref="dropdown"
         x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed z-50 {{ $width }} rounded-md shadow-lg"
         style="display: none;"
         @click="close()"
         role="menu"
         aria-orientation="vertical">
        <div class="rounded-md ring-1 ring-black ring-opacity-5 {{ $contentClasses }}">
            {{ $content }}
        </div>
    </div>
</div>

@once
@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('dropdown', (config = {}) => ({
        open: false,
        align: config.align || 'right',

        toggle() {
            this.open = !this.open;
            if (this.open) {
                this.$nextTick(() => this.positionDropdown());
            }
        },

        close() {
            this.open = false;
        },

        positionDropdown() {
            const trigger = this.$refs.trigger;
            const dropdown = this.$refs.dropdown;
            if (!trigger || !dropdown) return;

            const rect = trigger.getBoundingClientRect();
            const dropdownRect = dropdown.getBoundingClientRect();
            const viewportHeight = window.innerHeight;
            const viewportWidth = window.innerWidth;

            // Vertical positioning - check if dropdown fits below
            const spaceBelow = viewportHeight - rect.bottom;
            const spaceAbove = rect.top;
            const dropdownHeight = dropdown.offsetHeight || 200;

            let top;
            if (spaceBelow >= dropdownHeight || spaceBelow >= spaceAbove) {
                // Position below
                top = rect.bottom + 8;
            } else {
                // Position above
                top = rect.top - dropdownHeight - 8;
            }

            // Horizontal positioning based on align
            let left;
            if (this.align === 'left') {
                left = rect.left;
            } else if (this.align === 'right') {
                left = rect.right - dropdown.offsetWidth;
            } else {
                // center/top
                left = rect.left + (rect.width / 2) - (dropdown.offsetWidth / 2);
            }

            // Ensure dropdown stays within viewport horizontally
            if (left < 8) {
                left = 8;
            } else if (left + dropdown.offsetWidth > viewportWidth - 8) {
                left = viewportWidth - dropdown.offsetWidth - 8;
            }

            dropdown.style.top = top + 'px';
            dropdown.style.left = left + 'px';
        },

        init() {
            // Reposition on scroll/resize while open
            window.addEventListener('scroll', () => {
                if (this.open) this.positionDropdown();
            }, true);
            window.addEventListener('resize', () => {
                if (this.open) this.positionDropdown();
            });
        }
    }));
});
</script>
@endpush
@endonce
