<div class="space-y-4">
    <!-- Upload Attachment -->
    <div x-data="{ uploading: false }">
        <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-slate-300 border-dashed rounded-lg cursor-pointer bg-slate-50 hover:bg-slate-100 transition-colors">
            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                <svg class="w-8 h-8 mb-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
                <p class="mb-2 text-sm text-slate-500">
                    <span class="font-semibold">{{ __('Click to upload') }}</span> {{ __('or drag and drop') }}
                </p>
                <p class="text-xs text-slate-400">{{ __('Any file type') }}</p>
            </div>
            <input
                type="file"
                class="hidden"
                @change="
                    uploading = true;
                    $el.closest('[x-data*=taskSidePanel]').__x.$data.uploadAttachment($event.target.files[0])
                        .then(() => { uploading = false; $event.target.value = ''; })
                        .catch(() => { uploading = false; $event.target.value = ''; });
                "
            />
        </label>
        <div x-show="uploading" class="mt-2 text-sm text-blue-600 text-center">
            {{ __('Uploading...') }}
        </div>
    </div>

    <!-- Attachments List -->
    <div class="space-y-2">
        <template x-for="attachment in $el.closest('[x-data*=taskSidePanel]').__x.$data.task.attachments" :key="attachment.id">
            <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-lg hover:bg-slate-100 transition-colors group">
                <!-- File Icon -->
                <div class="flex-shrink-0">
                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>

                <!-- File Details -->
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-medium text-slate-900 truncate" x-text="attachment.file_name"></div>
                    <div class="flex items-center gap-2 text-xs text-slate-500 mt-1">
                        <span x-text="attachment.file_size_formatted || 'Unknown size'"></span>
                        <span>•</span>
                        <span x-text="attachment.user?.name"></span>
                        <span>•</span>
                        <span x-text="new Date(attachment.created_at).toLocaleDateString()"></span>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                    <a
                        :href="`/tasks/attachments/${attachment.id}/download`"
                        class="p-2 text-blue-600 hover:bg-blue-50 rounded"
                        title="{{ __('Download') }}"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                    </a>
                    <button
                        @click="if(confirm('Delete this attachment?')) $el.closest('[x-data*=taskSidePanel]').__x.$data.deleteAttachment(attachment.id)"
                        class="p-2 text-red-600 hover:bg-red-50 rounded"
                        title="{{ __('Delete') }}"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>
            </div>
        </template>

        <template x-if="!$el.closest('[x-data*=taskSidePanel]').__x.$data.task.attachments?.length">
            <div class="text-center py-8 text-slate-400 text-sm">
                {{ __('No attachments yet') }}
            </div>
        </template>
    </div>
</div>

<script>
// Extend taskSidePanel with attachment methods
document.addEventListener('alpine:init', () => {
    if (window.taskSidePanelAttachmentsExtended) return;
    window.taskSidePanelAttachmentsExtended = true;

    const originalFunction = window.taskSidePanel;
    window.taskSidePanel = function() {
        const instance = originalFunction();

        instance.uploadAttachment = async function(file) {
            if (!this.task || !file) return;

            const formData = new FormData();
            formData.append('file', file);

            try {
                const response = await fetch(`/tasks/${this.task.id}/attachments`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                });

                if (response.ok) {
                    const attachment = await response.json();
                    this.task.attachments = this.task.attachments || [];
                    this.task.attachments.unshift(attachment); // Add to beginning
                } else {
                    alert('Failed to upload file');
                }
            } catch (error) {
                console.error('Error uploading attachment:', error);
                alert('An error occurred while uploading');
                throw error;
            }
        };

        instance.deleteAttachment = async function(attachmentId) {
            if (!this.task) return;

            try {
                const response = await fetch(`/tasks/attachments/${attachmentId}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                if (response.ok) {
                    this.task.attachments = this.task.attachments.filter(a => a.id !== attachmentId);
                }
            } catch (error) {
                console.error('Error deleting attachment:', error);
            }
        };

        return instance;
    };
});
</script>
