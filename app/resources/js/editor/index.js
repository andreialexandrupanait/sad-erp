/**
 * Template Editor - Main Entry Point
 *
 * This file registers the template editor with Alpine.js.
 * It should be loaded on pages that need the template editor.
 *
 * NOTE: Alpine is already loaded globally by the app, so we use window.Alpine
 * instead of importing it to avoid "multiple instances" errors.
 */

import { templateEditor } from './templateEditor'

// Register the template editor component with Alpine (use global instance)
if (window.Alpine) {
    window.Alpine.data('templateEditor', templateEditor)
} else {
    // If Alpine hasn't loaded yet, wait for it
    document.addEventListener('alpine:init', () => {
        window.Alpine.data('templateEditor', templateEditor)
    })
}

// Export for use in other scripts if needed
window.templateEditor = templateEditor

export { templateEditor }
