import './bootstrap';

// Import Alpine plugins (Alpine itself is bundled with Livewire v3)
import collapse from '@alpinejs/collapse';

// Import components (they set window.* globals)
import './bulk-selection.js';
import './clients-page.js';

// Import Alpine components
import categoryCombobox from './components/category-combobox.js';

// Register Alpine plugins and components before Alpine starts
// NOTE: Alpine is bundled with Livewire v3, we just add plugins
document.addEventListener('alpine:init', () => {
    Alpine.plugin(collapse);

    // Register categoryCombobox as an Alpine data component
    // This allows x-data="categoryCombobox({...})" to work
    Alpine.data('categoryCombobox', categoryCombobox);
});
