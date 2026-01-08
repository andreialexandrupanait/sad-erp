import './bootstrap';

// Import components
import './bulk-selection.js';
import './clients-page.js';

// Import template editor (conditionally used on template edit pages)
import { templateEditor } from './editor/templateEditor';

// Make templateEditor globally available
window.templateEditor = templateEditor;
