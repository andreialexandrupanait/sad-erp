{{-- Command Palette Component --}}
<div x-data="commandPalette()" x-init="init()"
     @open-command-palette.window="open()"
     @keydown.escape.window="if (isOpen) close()"
     x-show="isOpen"
     x-cloak
     class="fixed inset-0 z-[100]"
     role="dialog"
     aria-modal="true"
     aria-labelledby="command-palette-title">

    {{-- Backdrop --}}
    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm"
         x-show="isOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="close()">
    </div>

    {{-- Dialog --}}
    <div class="fixed inset-0 flex items-start justify-center pt-[15vh] px-4">
        <div class="w-full max-w-xl bg-white rounded-xl shadow-2xl overflow-hidden"
             x-show="isOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             @click.outside="close()">

            {{-- Search Input --}}
            <div class="relative border-b border-slate-200">
                <svg class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text"
                       x-ref="searchInput"
                       x-model="query"
                       @keydown.arrow-down.prevent="moveDown()"
                       @keydown.arrow-up.prevent="moveUp()"
                       @keydown.enter.prevent="selectItem()"
                       placeholder="{{ __('Search pages, actions...') }}"
                       class="w-full pl-12 pr-4 py-4 text-lg text-slate-900 placeholder-slate-400 focus:outline-none"
                       id="command-palette-title">
                <div class="absolute right-4 top-1/2 -translate-y-1/2">
                    <kbd class="px-2 py-1 text-xs font-medium text-slate-500 bg-slate-100 rounded">ESC</kbd>
                </div>
            </div>

            {{-- Results --}}
            <div class="max-h-80 overflow-y-auto py-2" x-ref="resultsList">
                <template x-if="filteredItems.length === 0">
                    <div class="px-4 py-8 text-center text-slate-500">
                        <svg class="mx-auto h-12 w-12 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p>{{ __('No results found') }}</p>
                    </div>
                </template>

                <template x-for="(group, groupIndex) in groupedItems" :key="groupIndex">
                    <div>
                        <div class="px-4 py-2 text-xs font-semibold text-slate-500 uppercase tracking-wider" x-text="group.name"></div>
                        <template x-for="(item, itemIndex) in group.items" :key="item.id">
                            <button @click="navigate(item)"
                                    @mouseenter="selectedIndex = getGlobalIndex(groupIndex, itemIndex)"
                                    :class="selectedIndex === getGlobalIndex(groupIndex, itemIndex) ? 'bg-slate-100' : ''"
                                    class="w-full px-4 py-2 flex items-center gap-3 text-left hover:bg-slate-50 transition-colors">
                                <span class="w-8 h-8 flex items-center justify-center rounded-md bg-slate-100 text-slate-600" x-html="item.icon"></span>
                                <span class="flex-1">
                                    <span class="block text-sm font-medium text-slate-900" x-text="item.name"></span>
                                    <span class="block text-xs text-slate-500" x-text="item.description" x-show="item.description"></span>
                                </span>
                                <span class="text-xs text-slate-400" x-text="item.shortcut" x-show="item.shortcut"></span>
                            </button>
                        </template>
                    </div>
                </template>
            </div>

            {{-- Footer --}}
            <div class="border-t border-slate-200 px-4 py-2 flex items-center gap-4 text-xs text-slate-500 bg-slate-50">
                <span class="flex items-center gap-1">
                    <kbd class="px-1.5 py-0.5 bg-white border border-slate-300 rounded text-slate-600">↵</kbd>
                    {{ __('Select') }}
                </span>
                <span class="flex items-center gap-1">
                    <kbd class="px-1.5 py-0.5 bg-white border border-slate-300 rounded text-slate-600">↑</kbd>
                    <kbd class="px-1.5 py-0.5 bg-white border border-slate-300 rounded text-slate-600">↓</kbd>
                    {{ __('Navigate') }}
                </span>
            </div>
        </div>
    </div>
</div>

<script>
// Global keyboard listener for Ctrl+K / Cmd+K - runs immediately
document.addEventListener('keydown', function(event) {
    if ((event.ctrlKey || event.metaKey) && event.key === 'k') {
        event.preventDefault();
        // Dispatch custom event that Alpine component will listen for
        window.dispatchEvent(new CustomEvent('open-command-palette'));
    }
});

function commandPalette() {
    return {
        isOpen: false,
        query: '',
        selectedIndex: 0,
        items: [
            // Navigation
            { id: 'dashboard', name: '{{ __("Dashboard") }}', description: '{{ __("Main dashboard") }}', url: '{{ route("dashboard") }}', group: '{{ __("Navigation") }}', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>' },
            { id: 'clients', name: '{{ __("Clients") }}', description: '{{ __("Manage clients") }}', url: '{{ route("clients.index") }}', group: '{{ __("Navigation") }}', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>' },
            { id: 'credentials', name: '{{ __("Credentials") }}', description: '{{ __("Access credentials") }}', url: '{{ route("credentials.index") }}', group: '{{ __("Navigation") }}', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>' },
            { id: 'offers', name: '{{ __("Offers") }}', description: '{{ __("Sales offers") }}', url: '{{ route("offers.index") }}', group: '{{ __("Navigation") }}', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>' },
            { id: 'contracts', name: '{{ __("Contracts") }}', description: '{{ __("Manage contracts") }}', url: '{{ route("contracts.index") }}', group: '{{ __("Navigation") }}', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>' },

            // Financial
            { id: 'financial', name: '{{ __("Financial Dashboard") }}', description: '{{ __("Financial overview") }}', url: '{{ route("financial.dashboard") }}', group: '{{ __("Financial") }}', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>' },
            { id: 'revenues', name: '{{ __("Revenues") }}', description: '{{ __("Income tracking") }}', url: '{{ route("financial.revenues.index") }}', group: '{{ __("Financial") }}', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>' },
            { id: 'expenses', name: '{{ __("Expenses") }}', description: '{{ __("Expense tracking") }}', url: '{{ route("financial.expenses.index") }}', group: '{{ __("Financial") }}', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>' },

            // Resources
            { id: 'domains', name: '{{ __("Domains") }}', description: '{{ __("Domain management") }}', url: '{{ route("domains.index") }}', group: '{{ __("Resources") }}', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9"/></svg>' },
            { id: 'subscriptions', name: '{{ __("Subscriptions") }}', description: '{{ __("Subscription tracking") }}', url: '{{ route("subscriptions.index") }}', group: '{{ __("Resources") }}', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>' },

            // Actions
            { id: 'new-client', name: '{{ __("New Client") }}', description: '{{ __("Create a new client") }}', url: '{{ route("clients.create") }}', group: '{{ __("Quick Actions") }}', shortcut: 'N C', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>' },
            { id: 'new-offer', name: '{{ __("New Offer") }}', description: '{{ __("Create a new offer") }}', url: '{{ route("offers.create") }}', group: '{{ __("Quick Actions") }}', shortcut: 'N O', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>' },
            { id: 'new-expense', name: '{{ __("New Expense") }}', description: '{{ __("Record an expense") }}', url: '{{ route("financial.expenses.create") }}', group: '{{ __("Quick Actions") }}', shortcut: 'N E', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>' },
            { id: 'new-revenue', name: '{{ __("New Revenue") }}', description: '{{ __("Record revenue") }}', url: '{{ route("financial.revenues.create") }}', group: '{{ __("Quick Actions") }}', shortcut: 'N R', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>' },

            // Settings
            { id: 'settings', name: '{{ __("Settings") }}', description: '{{ __("Application settings") }}', url: '{{ route("settings.index") }}', group: '{{ __("System") }}', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>' },
            { id: 'profile', name: '{{ __("Profile") }}', description: '{{ __("Your profile") }}', url: '{{ route("profile.edit") }}', group: '{{ __("System") }}', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>' },
        ],

        init() {
            this.$watch('query', () => {
                this.selectedIndex = 0;
            });
        },

        open() {
            this.isOpen = true;
            this.query = '';
            this.selectedIndex = 0;
            this.$nextTick(() => {
                this.$refs.searchInput.focus();
            });
        },

        close() {
            this.isOpen = false;
            this.query = '';
        },

        get filteredItems() {
            if (!this.query) return this.items;
            const q = this.query.toLowerCase();
            return this.items.filter(item =>
                item.name.toLowerCase().includes(q) ||
                (item.description && item.description.toLowerCase().includes(q))
            );
        },

        get groupedItems() {
            const groups = {};
            this.filteredItems.forEach(item => {
                if (!groups[item.group]) {
                    groups[item.group] = { name: item.group, items: [] };
                }
                groups[item.group].items.push(item);
            });
            return Object.values(groups);
        },

        getGlobalIndex(groupIndex, itemIndex) {
            let index = 0;
            for (let i = 0; i < groupIndex; i++) {
                index += this.groupedItems[i].items.length;
            }
            return index + itemIndex;
        },

        moveDown() {
            if (this.selectedIndex < this.filteredItems.length - 1) {
                this.selectedIndex++;
                this.scrollToSelected();
            }
        },

        moveUp() {
            if (this.selectedIndex > 0) {
                this.selectedIndex--;
                this.scrollToSelected();
            }
        },

        scrollToSelected() {
            this.$nextTick(() => {
                const list = this.$refs.resultsList;
                const selected = list.querySelector(`button:nth-child(${this.selectedIndex + 1})`);
                if (selected) {
                    selected.scrollIntoView({ block: 'nearest' });
                }
            });
        },

        selectItem() {
            const item = this.filteredItems[this.selectedIndex];
            if (item) {
                this.navigate(item);
            }
        },

        navigate(item) {
            if (item.url) {
                window.location.href = item.url;
            }
            this.close();
        }
    };
}
</script>
