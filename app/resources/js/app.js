import './bootstrap';

// Import Alpine plugins (Alpine itself is bundled with Livewire v3)
import collapse from '@alpinejs/collapse';

// Import components (they set window.* globals)
import './bulk-selection.js';
import './clients-page.js';

// Import template editor and export to window
import { templateEditor } from './editor/templateEditor';
window.templateEditor = templateEditor;

// Register Alpine plugins before Alpine starts
// NOTE: Alpine is bundled with Livewire v3, we just add plugins
document.addEventListener('alpine:init', () => {
    Alpine.plugin(collapse);
});
