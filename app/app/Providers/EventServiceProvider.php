<?php

namespace App\Providers;

use App\Events\Task\TaskAssigned;
use App\Events\Task\TaskCreated;
use App\Events\Task\TaskDeleted;
use App\Events\Task\TaskStatusChanged;
use App\Events\Task\TaskUpdated;
use App\Listeners\Task\LogTaskActivity;
use App\Listeners\Task\SendTaskNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        TaskCreated::class => [
            LogTaskActivity::class . '@handleTaskCreated',
            SendTaskNotification::class . '@handleTaskCreated',
        ],
        TaskUpdated::class => [
            LogTaskActivity::class . '@handleTaskUpdated',
        ],
        TaskDeleted::class => [
            LogTaskActivity::class . '@handleTaskDeleted',
        ],
        TaskStatusChanged::class => [
            LogTaskActivity::class . '@handleTaskStatusChanged',
            SendTaskNotification::class . '@handleTaskStatusChanged',
        ],
        TaskAssigned::class => [
            LogTaskActivity::class . '@handleTaskAssigned',
            SendTaskNotification::class . '@handleTaskAssigned',
        ],
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
