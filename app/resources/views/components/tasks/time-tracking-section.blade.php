<div x-data="{
    entries: [],
    summary: {},
    runningTimer: null,
    loading: false,
    showAddForm: false,
    editingEntry: null,
    form: {
        description: '',
        minutes: '',
        hours: '',
        billable: true
    },

    get taskId() {
        return task?.id || null;
    },

    init() {
        this.loadEntries();
        this.checkRunningTimer();
        // Check for running timer every 30 seconds
        setInterval(() => this.checkRunningTimer(), 30000);
    },

    async loadEntries() {
        if (!this.taskId) return;
        this.loading = true;
        try {
            const response = await fetch(`/tasks/${this.taskId}/time-entries`);
            const data = await response.json();
            if (data.success) {
                this.entries = data.entries;
                this.summary = data.summary;
            }
        } catch (error) {
            console.error('Failed to load time entries:', error);
        } finally {
            this.loading = false;
        }
    },

    async checkRunningTimer() {
        if (!this.taskId) return;
        try {
            const response = await fetch('/time-entries/running');
            const data = await response.json();
            if (data.success && data.timer && data.timer.task_id === this.taskId) {
                this.runningTimer = data.timer;
            } else {
                this.runningTimer = null;
            }
        } catch (error) {
            console.error('Failed to check running timer:', error);
        }
    },

    async startTimer() {
        if (!this.taskId) return;
        try {
            const response = await fetch(`/tasks/${this.taskId}/time-entries/start`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                },
                body: JSON.stringify({
                    description: this.form.description || null
                })
            });

            const data = await response.json();
            if (data.success) {
                this.runningTimer = data.entry;
                this.form.description = '';
                showToast('Timer started', 'success');
            } else {
                showToast(data.message || 'Failed to start timer', 'error');
            }
        } catch (error) {
            console.error('Failed to start timer:', error);
            showToast('Failed to start timer', 'error');
        }
    },

    async stopTimer() {
        if (!this.runningTimer || !this.taskId) return;

        try {
            const response = await fetch(`/tasks/${this.taskId}/time-entries/${this.runningTimer.id}/stop`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                }
            });

            const data = await response.json();
            if (data.success) {
                this.runningTimer = null;
                await this.loadEntries();
                showToast(data.message, 'success');
            } else {
                showToast(data.message || 'Failed to stop timer', 'error');
            }
        } catch (error) {
            console.error('Failed to stop timer:', error);
            showToast('Failed to stop timer', 'error');
        }
    },

    getElapsedTime() {
        if (!this.runningTimer || !this.runningTimer.started_at) return '0m';

        const start = new Date(this.runningTimer.started_at);
        const now = new Date();
        const diffMs = now - start;
        const diffMins = Math.floor(diffMs / 60000);

        const hours = Math.floor(diffMins / 60);
        const mins = diffMins % 60;

        if (hours > 0) {
            return `${hours}h ${mins}m`;
        }
        return `${mins}m`;
    },

    openAddForm() {
        this.showAddForm = true;
        this.editingEntry = null;
        this.form = {
            description: '',
            minutes: '',
            hours: '',
            billable: true
        };
    },

    openEditForm(entry) {
        this.editingEntry = entry;
        this.showAddForm = true;
        const hours = Math.floor(entry.minutes / 60);
        const mins = entry.minutes % 60;
        this.form = {
            description: entry.description || '',
            hours: hours > 0 ? hours : '',
            minutes: mins > 0 ? mins : '',
            billable: entry.billable
        };
    },

    closeForm() {
        this.showAddForm = false;
        this.editingEntry = null;
        this.form = {
            description: '',
            minutes: '',
            hours: '',
            billable: true
        };
    },

    async saveEntry() {
        if (!this.taskId) return;

        const totalMinutes = (parseInt(this.form.hours) || 0) * 60 + (parseInt(this.form.minutes) || 0);

        if (totalMinutes <= 0) {
            showToast('Please enter a valid time duration', 'error');
            return;
        }

        const url = this.editingEntry
            ? `/tasks/${this.taskId}/time-entries/${this.editingEntry.id}`
            : `/tasks/${this.taskId}/time-entries`;
        const method = this.editingEntry ? 'PATCH' : 'POST';

        try {
            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                },
                body: JSON.stringify({
                    description: this.form.description || null,
                    minutes: totalMinutes,
                    billable: this.form.billable
                })
            });

            const data = await response.json();
            if (data.success) {
                await this.loadEntries();
                this.closeForm();
                showToast(data.message, 'success');
            } else {
                showToast(data.message || 'Failed to save time entry', 'error');
            }
        } catch (error) {
            console.error('Failed to save time entry:', error);
            showToast('Failed to save time entry', 'error');
        }
    },

    async deleteEntry(entry) {
        if (!this.taskId || !confirm('Are you sure you want to delete this time entry?')) return;

        try {
            const response = await fetch(`/tasks/${this.taskId}/time-entries/${entry.id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                }
            });

            const data = await response.json();
            if (data.success) {
                await this.loadEntries();
                showToast(data.message, 'success');
            } else {
                showToast(data.message || 'Failed to delete time entry', 'error');
            }
        } catch (error) {
            console.error('Failed to delete time entry:', error);
            showToast('Failed to delete time entry', 'error');
        }
    }
}" class="space-y-4">

    <!-- Running Timer Display -->
    <div x-show="runningTimer" x-cloak class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-3 h-3 bg-red-500 rounded-full animate-pulse"></div>
                <div>
                    <div class="text-sm font-medium text-gray-900">Timer Running</div>
                    <div class="text-xs text-gray-500" x-text="runningTimer?.description || 'No description'"></div>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <div class="text-xl font-mono font-bold text-blue-600"
                     x-text="getElapsedTime()"
                     x-init="setInterval(() => $el.textContent = getElapsedTime(), 1000)"></div>
                <button @click="stopTimer()"
                        class="px-3 py-1 bg-red-600 text-white text-sm rounded hover:bg-red-700">
                    Stop
                </button>
            </div>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-[#fafafa] rounded-lg p-3">
            <div class="text-xs text-gray-500 uppercase">Total</div>
            <div class="text-lg font-semibold text-gray-900" x-text="summary.total_formatted || '0m'"></div>
        </div>
        <div class="bg-green-50 rounded-lg p-3">
            <div class="text-xs text-green-600 uppercase">Billable</div>
            <div class="text-lg font-semibold text-green-700" x-text="summary.billable_formatted || '0m'"></div>
        </div>
        <div class="bg-[#fafafa] rounded-lg p-3">
            <div class="text-xs text-gray-500 uppercase">Non-billable</div>
            <div class="text-lg font-semibold text-gray-600" x-text="summary.non_billable_formatted || '0m'"></div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex items-center space-x-2">
        <button @click="startTimer()"
                x-show="!runningTimer"
                class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center justify-center space-x-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>Start Timer</span>
        </button>

        <button @click="openAddForm()"
                class="flex-1 px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-[#fafafa] flex items-center justify-center space-x-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span>Add Time</span>
        </button>
    </div>

    <!-- Add/Edit Form Modal -->
    <div x-show="showAddForm"
         x-cloak
         @click.self="closeForm()"
         class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold" x-text="editingEntry ? 'Edit Time Entry' : 'Add Time Entry'"></h3>
                <button @click="closeForm()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="space-y-4">
                <!-- Time Duration -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Time Duration</label>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <input type="number"
                                   x-model="form.hours"
                                   min="0"
                                   placeholder="Hours"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <span class="text-xs text-gray-500 mt-1">Hours</span>
                        </div>
                        <div>
                            <input type="number"
                                   x-model="form.minutes"
                                   min="0"
                                   max="59"
                                   placeholder="Minutes"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <span class="text-xs text-gray-500 mt-1">Minutes</span>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description (optional)</label>
                    <textarea x-model="form.description"
                              rows="3"
                              placeholder="What did you work on?"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                </div>

                <!-- Billable Toggle -->
                <div class="flex items-center justify-between">
                    <label class="text-sm font-medium text-gray-700">Billable</label>
                    <button @click="form.billable = !form.billable"
                            :class="form.billable ? 'bg-green-600' : 'bg-gray-300'"
                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors">
                        <span :class="form.billable ? 'translate-x-6' : 'translate-x-1'"
                              class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"></span>
                    </button>
                </div>
            </div>

            <div class="flex items-center space-x-3 pt-4 border-t">
                <button @click="closeForm()"
                        class="flex-1 px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-[#fafafa]">
                    Cancel
                </button>
                <button @click="saveEntry()"
                        class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Save
                </button>
            </div>
        </div>
    </div>

    <!-- Time Entries List -->
    <div class="space-y-2">
        <h3 class="text-sm font-medium text-gray-700 uppercase">Time Entries</h3>

        <div x-show="loading" class="text-center py-8 text-gray-500">
            Loading...
        </div>

        <div x-show="!loading && entries.length === 0" x-cloak class="text-center py-8 text-gray-500">
            <svg class="w-12 h-12 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p>No time entries yet</p>
        </div>

        <div x-show="!loading" class="space-y-2">
            <template x-for="entry in entries" :key="entry.id">
                <div class="bg-[#fafafa] rounded-lg p-3 hover:bg-gray-50">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center space-x-2 mb-1">
                                <span class="font-medium text-gray-900" x-text="entry.formatted_duration"></span>
                                <span x-show="entry.billable"
                                      class="px-2 py-0.5 bg-green-100 text-green-700 text-xs rounded">
                                    Billable
                                </span>
                                <span x-show="!entry.billable"
                                      class="px-2 py-0.5 bg-gray-200 text-gray-600 text-xs rounded">
                                    Non-billable
                                </span>
                            </div>
                            <div x-show="entry.description"
                                 class="text-sm text-gray-600 mb-1"
                                 x-text="entry.description"></div>
                            <div class="flex items-center space-x-2 text-xs text-gray-500">
                                <span x-text="entry.user?.name || 'Unknown'"></span>
                                <span>•</span>
                                <span x-text="new Date(entry.created_at).toLocaleString()"></span>
                            </div>
                        </div>
                        <div class="flex items-center space-x-1 ml-2">
                            <button @click="openEditForm(entry)"
                                    class="p-1 text-gray-400 hover:text-blue-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                            <button @click="deleteEntry(entry)"
                                    class="p-1 text-gray-400 hover:text-red-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>
