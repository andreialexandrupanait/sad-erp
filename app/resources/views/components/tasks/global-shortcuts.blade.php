<script>
document.addEventListener('alpine:init', () => {
    // Global keyboard shortcuts
    document.addEventListener('keydown', (e) => {
        // Ignore if user is typing in an input/textarea
        if (['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName)) {
            return;
        }

        // N - New Task
        if (e.key === 'n' || e.key === 'N') {
            e.preventDefault();
            window.location.href = '{{ route('tasks.create') }}';
        }

        // Cmd/Ctrl + A - Select All Tasks
        if ((e.metaKey || e.ctrlKey) && e.key === 'a') {
            e.preventDefault();
            const checkboxes = document.querySelectorAll('input[type="checkbox"][x-model*="taskBulk"]');
            if (checkboxes.length > 0) {
                const selectAllCheckbox = document.querySelector('th input[type="checkbox"]');
                if (selectAllCheckbox) {
                    selectAllCheckbox.click();
                }
            }
        }

        // ESC - Clear bulk selection
        if (e.key === 'Escape') {
            if (Alpine.store('taskBulk') && Alpine.store('taskBulk').selectedTasks.length > 0) {
                Alpine.store('taskBulk').clearSelection();
            }
        }
    });
});
</script>
