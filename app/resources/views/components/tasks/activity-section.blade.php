<div x-data="activityManager()" x-init="loadActivities()" class="p-4">
    <!-- Loading State -->
    <div x-show="loading" x-cloak class="flex justify-center py-8">
        <svg class="animate-spin h-8 w-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    </div>

    <!-- Activity Timeline -->
    <div x-show="!loading" x-cloak>
        <template x-if="activities.length === 0">
            <div class="text-center py-12">
                <svg class="w-16 h-16 mx-auto text-slate-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-slate-600 font-medium">{{ __('No activity yet') }}</p>
                <p class="text-slate-500 text-sm mt-1">{{ __('Activity will appear here as changes are made') }}</p>
            </div>
        </template>

        <!-- Activity List -->
        <div class="space-y-4">
            <template x-for="(activity, index) in activities" :key="activity.id">
                <div class="relative">
                    <!-- Timeline Line -->
                    <template x-if="index < activities.length - 1">
                        <div class="absolute left-4 top-8 bottom-0 w-0.5 bg-slate-200"></div>
                    </template>

                    <!-- Activity Item -->
                    <div class="flex gap-3">
                        <!-- Avatar -->
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-medium text-sm"
                                 x-text="activity.user?.name?.charAt(0).toUpperCase() || '?'">
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0 bg-[#fafafa] rounded-lg p-3">
                            <!-- Description -->
                            <div class="text-sm text-slate-900 mb-1">
                                <span class="font-medium" x-text="activity.user?.name || 'Unknown'"></span>
                                <span class="text-slate-600">
                                    <template x-if="activity.action === 'created'">
                                        <span>{{ __('created this task') }}</span>
                                    </template>
                                    <template x-if="activity.action === 'status_changed'">
                                        <span>
                                            {{ __('changed status from') }}
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium"
                                                  :style="`background-color: ${activity.metadata?.old_color}20; color: ${activity.metadata?.old_color}`"
                                                  x-text="activity.old_value"></span>
                                            {{ __('to') }}
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium"
                                                  :style="`background-color: ${activity.metadata?.new_color}20; color: ${activity.metadata?.new_color}`"
                                                  x-text="activity.new_value"></span>
                                        </span>
                                    </template>
                                    <template x-if="activity.action === 'priority_changed'">
                                        <span>
                                            {{ __('changed priority from') }}
                                            <span class="font-medium" x-text="activity.old_value"></span>
                                            {{ __('to') }}
                                            <span class="font-medium" x-text="activity.new_value"></span>
                                        </span>
                                    </template>
                                    <template x-if="activity.action === 'assigned'">
                                        <span x-show="activity.new_value">
                                            {{ __('assigned to') }} <span class="font-medium" x-text="activity.new_value"></span>
                                        </span>
                                        <span x-show="!activity.new_value">
                                            {{ __('removed assignee') }} <span class="font-medium" x-text="activity.old_value"></span>
                                        </span>
                                    </template>
                                    <template x-if="activity.action === 'assignee_added'">
                                        <span>{{ __('added') }} <span class="font-medium" x-text="activity.new_value"></span> {{ __('as assignee') }}</span>
                                    </template>
                                    <template x-if="activity.action === 'assignee_removed'">
                                        <span>{{ __('removed') }} <span class="font-medium" x-text="activity.old_value"></span> {{ __('as assignee') }}</span>
                                    </template>
                                    <template x-if="activity.action === 'watcher_added'">
                                        <span>{{ __('added') }} <span class="font-medium" x-text="activity.new_value"></span> {{ __('as watcher') }}</span>
                                    </template>
                                    <template x-if="activity.action === 'watcher_removed'">
                                        <span>{{ __('removed') }} <span class="font-medium" x-text="activity.old_value"></span> {{ __('as watcher') }}</span>
                                    </template>
                                    <template x-if="activity.action === 'date_changed'">
                                        <span>
                                            {{ __('changed') }} <span x-text="activity.field_changed.replace('_', ' ')"></span>
                                            {{ __('from') }} <span class="font-medium" x-text="activity.old_value || 'none'"></span>
                                            {{ __('to') }} <span class="font-medium" x-text="activity.new_value || 'none'"></span>
                                        </span>
                                    </template>
                                    <template x-if="activity.action === 'comment_added'">
                                        <span>{{ __('added a comment') }}</span>
                                    </template>
                                    <template x-if="activity.action === 'attachment_added'">
                                        <span>{{ __('added attachment') }} <span class="font-medium" x-text="activity.new_value"></span></span>
                                    </template>
                                    <template x-if="activity.action === 'tag_added'">
                                        <span>
                                            {{ __('added tag') }}
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ml-1"
                                                  :style="`background-color: ${activity.metadata?.tag_color}20; color: ${activity.metadata?.tag_color}`"
                                                  x-text="activity.new_value"></span>
                                        </span>
                                    </template>
                                    <template x-if="activity.action === 'tag_removed'">
                                        <span>
                                            {{ __('removed tag') }}
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ml-1"
                                                  :style="`background-color: ${activity.metadata?.tag_color}20; color: ${activity.metadata?.tag_color}`"
                                                  x-text="activity.old_value"></span>
                                        </span>
                                    </template>
                                    <template x-if="activity.action === 'dependency_added'">
                                        <span>{{ __('added dependency on') }} <span class="font-medium" x-text="activity.new_value"></span></span>
                                    </template>
                                    <template x-if="activity.action === 'dependency_removed'">
                                        <span>{{ __('removed dependency on') }} <span class="font-medium" x-text="activity.old_value"></span></span>
                                    </template>
                                    <template x-if="activity.action === 'updated'">
                                        <span>{{ __('updated') }} <span x-text="activity.field_changed?.replace('_', ' ')"></span></span>
                                    </template>
                                </span>
                            </div>

                            <!-- Timestamp -->
                            <div class="text-xs text-slate-500" x-text="formatTime(activity.created_at)"></div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>

@push('scripts')
<script>
function activityManager() {
    return {
        activities: [],
        loading: true,

        async loadActivities() {
            const taskId = Alpine.store('sidePanel').taskId;
            if (!taskId) return;

            this.loading = true;

            try {
                const response = await fetch(`/tasks/${taskId}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    }
                });

                const task = await response.json();
                this.activities = task.activities || [];
            } catch (error) {
                console.error('Error loading activities:', error);
            } finally {
                this.loading = false;
            }
        },

        formatTime(timestamp) {
            if (!timestamp) return '';

            const date = new Date(timestamp);
            const now = new Date();
            const diffInSeconds = Math.floor((now - date) / 1000);

            if (diffInSeconds < 60) {
                return 'just now';
            } else if (diffInSeconds < 3600) {
                const minutes = Math.floor(diffInSeconds / 60);
                return `${minutes} minute${minutes > 1 ? 's' : ''} ago`;
            } else if (diffInSeconds < 86400) {
                const hours = Math.floor(diffInSeconds / 3600);
                return `${hours} hour${hours > 1 ? 's' : ''} ago`;
            } else if (diffInSeconds < 604800) {
                const days = Math.floor(diffInSeconds / 86400);
                return `${days} day${days > 1 ? 's' : ''} ago`;
            } else {
                return date.toLocaleDateString('en-US', {
                    month: 'short',
                    day: 'numeric',
                    year: date.getFullYear() !== now.getFullYear() ? 'numeric' : undefined
                });
            }
        }
    };
}
</script>
@endpush
