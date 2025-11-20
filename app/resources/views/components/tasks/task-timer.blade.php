@props(['task'])

<!-- Real-Time Task Timer -->
<div
    x-data="taskTimer({{ $task->id }}, {{ $task->time_tracked ?? 0 }})"
    x-init="init()"
    class="flex items-center gap-2"
>
    <!-- Timer Display -->
    <div class="flex items-center gap-2">
        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span class="text-sm font-mono" :class="{ 'text-blue-600 font-semibold': isRunning }" x-text="displayTime"></span>
    </div>

    <!-- Timer Controls -->
    <div class="flex items-center gap-1">
        <template x-if="!isRunning">
            <button
                @click="start()"
                class="p-1 rounded hover:bg-slate-100 text-green-600 hover:text-green-700"
                title="{{ __('Start Timer') }}"
            >
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M8 5v14l11-7z"/>
                </svg>
            </button>
        </template>

        <template x-if="isRunning">
            <button
                @click="stop()"
                class="p-1 rounded hover:bg-slate-100 text-red-600 hover:text-red-700 animate-pulse"
                title="{{ __('Stop Timer') }}"
            >
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M6 4h4v16H6V4zm8 0h4v16h-4V4z"/>
                </svg>
            </button>
        </template>

        <!-- Manual Time Entry -->
        <button
            @click="showManualEntry = !showManualEntry"
            class="p-1 rounded hover:bg-slate-100 text-slate-600 hover:text-slate-700"
            title="{{ __('Manual Entry') }}"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
            </svg>
        </button>
    </div>

    <!-- Manual Time Entry Modal -->
    <div
        x-show="showManualEntry"
        @click.away="showManualEntry = false"
        x-cloak
        class="absolute z-10 mt-2 bg-white rounded-lg shadow-lg border border-slate-200 p-3 min-w-[200px]"
        style="top: 100%; right: 0;"
    >
        <div class="space-y-2">
            <label class="block text-xs font-medium text-slate-700">{{ __('Enter time (minutes)') }}</label>
            <input
                type="number"
                x-model="manualMinutes"
                @keydown.enter="setManualTime()"
                min="0"
                class="w-full px-2 py-1 text-sm border border-slate-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="0"
            />
            <div class="flex gap-2">
                <button
                    @click="setManualTime()"
                    class="flex-1 px-3 py-1 text-xs font-medium text-white bg-blue-600 hover:bg-blue-700 rounded"
                >
                    {{ __('Set') }}
                </button>
                <button
                    @click="showManualEntry = false"
                    class="px-3 py-1 text-xs font-medium text-slate-700 hover:bg-slate-100 rounded"
                >
                    {{ __('Cancel') }}
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function taskTimer(taskId, initialMinutes) {
    return {
        taskId: taskId,
        totalMinutes: parseInt(initialMinutes) || 0,
        isRunning: false,
        startTime: null,
        interval: null,
        displayTime: '',
        showManualEntry: false,
        manualMinutes: 0,

        init() {
            this.updateDisplay();

            // Check if this timer was running before (from localStorage)
            const savedTimer = this.getSavedTimer();
            if (savedTimer) {
                this.startTime = new Date(savedTimer.startTime);
                this.totalMinutes = savedTimer.totalMinutes;
                this.resume();
            }
        },

        start() {
            this.isRunning = true;
            this.startTime = new Date();
            this.saveTimer();

            this.interval = setInterval(() => {
                this.updateDisplay();
            }, 1000);
        },

        stop() {
            if (this.interval) {
                clearInterval(this.interval);
                this.interval = null;
            }

            // Calculate elapsed time
            if (this.startTime) {
                const elapsed = Math.floor((new Date() - this.startTime) / 60000);
                this.totalMinutes += elapsed;
            }

            this.isRunning = false;
            this.startTime = null;
            this.clearSavedTimer();
            this.saveToDatabase();
            this.updateDisplay();
        },

        resume() {
            this.isRunning = true;
            this.interval = setInterval(() => {
                this.updateDisplay();
            }, 1000);
        },

        updateDisplay() {
            let displayMinutes = this.totalMinutes;

            if (this.isRunning && this.startTime) {
                const elapsed = Math.floor((new Date() - this.startTime) / 60000);
                displayMinutes += elapsed;
            }

            const hours = Math.floor(displayMinutes / 60);
            const minutes = displayMinutes % 60;
            this.displayTime = `${hours}h ${minutes}m`;
        },

        async saveToDatabase() {
            try {
                const response = await fetch(`/tasks/${this.taskId}/quick-update`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        time_tracked: this.totalMinutes
                    })
                });

                const data = await response.json();
                if (!data.success) {
                    console.error('Failed to save time');
                }
            } catch (error) {
                console.error('Error saving time:', error);
            }
        },

        setManualTime() {
            const minutes = parseInt(this.manualMinutes) || 0;
            this.totalMinutes = minutes;
            this.saveToDatabase();
            this.updateDisplay();
            this.showManualEntry = false;
            this.manualMinutes = 0;
        },

        saveTimer() {
            localStorage.setItem(`task_timer_${this.taskId}`, JSON.stringify({
                startTime: this.startTime,
                totalMinutes: this.totalMinutes
            }));
        },

        getSavedTimer() {
            const saved = localStorage.getItem(`task_timer_${this.taskId}`);
            return saved ? JSON.parse(saved) : null;
        },

        clearSavedTimer() {
            localStorage.removeItem(`task_timer_${this.taskId}`);
        }
    };
}
</script>
