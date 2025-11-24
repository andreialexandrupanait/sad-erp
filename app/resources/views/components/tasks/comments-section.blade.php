<div class="space-y-4">
    <!-- Add Comment -->
    <div x-data="{ comment: '', submitting: false }">
        <textarea
            x-model="comment"
            rows="3"
            placeholder="{{ __('Write a comment...') }}"
            class="w-full px-3 py-2 border border-slate-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
        ></textarea>
        <div class="mt-2 flex justify-end">
            <button
                @click="
                    if (comment.trim()) {
                        submitting = true;
                        $el.closest('[x-data*=taskSidePanel]').__x.$data.addComment(comment)
                            .then(() => { comment = ''; submitting = false; })
                            .catch(() => { submitting = false; });
                    }
                "
                :disabled="!comment.trim() || submitting"
                class="px-4 py-2 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
                <span x-show="!submitting">{{ __('Comment') }}</span>
                <span x-show="submitting">{{ __('Posting...') }}</span>
            </button>
        </div>
    </div>

    <!-- Comments List -->
    <div class="space-y-4">
        <template x-for="comment in $el.closest('[x-data*=taskSidePanel]').__x.$data.task.comments" :key="comment.id">
            <div class="flex gap-3">
                <!-- Avatar -->
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-medium text-sm">
                        <span x-text="comment.user?.name?.charAt(0).toUpperCase()"></span>
                    </div>
                </div>

                <!-- Comment Content -->
                <div class="flex-1 min-w-0">
                    <div class="bg-[#fafafa] rounded-lg px-4 py-3">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="font-medium text-sm text-slate-900" x-text="comment.user?.name"></span>
                            <span class="text-xs text-slate-500" x-text="new Date(comment.created_at).toLocaleString()"></span>
                        </div>
                        <div class="text-sm text-slate-700 whitespace-pre-wrap" x-text="comment.comment"></div>
                    </div>

                    <!-- Comment Actions -->
                    <div class="mt-1 flex items-center gap-3 text-xs text-slate-500">
                        <button
                            x-data="{ replying: false, reply: '' }"
                            @click="replying = !replying"
                            class="hover:text-slate-700"
                        >
                            {{ __('Reply') }}
                        </button>
                        <button
                            @click="if(confirm('Delete this comment?')) $el.closest('[x-data*=taskSidePanel]').__x.$data.deleteComment(comment.id)"
                            class="hover:text-red-600"
                        >
                            {{ __('Delete') }}
                        </button>
                    </div>

                    <!-- Replies -->
                    <template x-if="comment.replies?.length">
                        <div class="mt-3 space-y-3 pl-4 border-l-2 border-slate-200">
                            <template x-for="reply in comment.replies" :key="reply.id">
                                <div class="flex gap-3">
                                    <div class="flex-shrink-0">
                                        <div class="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center text-slate-600 font-medium text-xs">
                                            <span x-text="reply.user?.name?.charAt(0).toUpperCase()"></span>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="bg-white rounded-lg px-3 py-2 border border-slate-200">
                                            <div class="flex items-center gap-2 mb-1">
                                                <span class="font-medium text-xs text-slate-900" x-text="reply.user?.name"></span>
                                                <span class="text-xs text-slate-400" x-text="new Date(reply.created_at).toLocaleString()"></span>
                                            </div>
                                            <div class="text-xs text-slate-700 whitespace-pre-wrap" x-text="reply.comment"></div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
        </template>

        <template x-if="!$el.closest('[x-data*=taskSidePanel]').__x.$data.task.comments?.length">
            <div class="text-center py-8 text-slate-400 text-sm">
                {{ __('No comments yet') }}
            </div>
        </template>
    </div>
</div>

<script>
// Extend taskSidePanel with comment methods
document.addEventListener('alpine:init', () => {
    if (window.taskSidePanelCommentsExtended) return;
    window.taskSidePanelCommentsExtended = true;

    const originalFunction = window.taskSidePanel;
    window.taskSidePanel = function() {
        const instance = originalFunction();

        instance.addComment = async function(commentText) {
            if (!this.task) return;

            try {
                const response = await fetch(`/tasks/${this.task.id}/comments`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ comment: commentText })
                });

                if (response.ok) {
                    const comment = await response.json();
                    this.task.comments = this.task.comments || [];
                    this.task.comments.unshift(comment); // Add to beginning
                }
            } catch (error) {
                console.error('Error adding comment:', error);
                throw error;
            }
        };

        instance.deleteComment = async function(commentId) {
            if (!this.task) return;

            try {
                const response = await fetch(`/tasks/comments/${commentId}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                if (response.ok) {
                    this.task.comments = this.task.comments.filter(c => c.id !== commentId);
                }
            } catch (error) {
                console.error('Error deleting comment:', error);
            }
        };

        return instance;
    };
});
</script>
