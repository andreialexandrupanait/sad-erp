<?php

namespace App\Http\View\Composers;

use App\Models\TaskSpace;
use Illuminate\View\View;

/**
 * Sidebar Composer
 *
 * Shares task workspace hierarchy data with the sidebar component
 */
class SidebarComposer
{
    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        // Load task spaces with their complete hierarchy
        $taskSpaces = TaskSpace::with([
            'folders' => function ($query) {
                $query->ordered()
                    ->with([
                        'lists' => function ($query) {
                            $query->ordered()
                                ->with('client')
                                ->withCount('tasks');
                        }
                    ]);
            }
        ])->ordered()->get();

        $view->with('taskSpaces', $taskSpaces);
    }
}
