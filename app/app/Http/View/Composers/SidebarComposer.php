<?php

namespace App\Http\View\Composers;

use App\Models\Module;
use Illuminate\View\View;

/**
 * Sidebar Composer
 *
 * Shares common data with the sidebar component including
 * accessible modules based on user permissions.
 */
class SidebarComposer
{
    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        $user = auth()->user();

        if (!$user) {
            $view->with('accessibleModules', collect());
            $view->with('modulePermissions', []);
            return;
        }

        // Get accessible modules for sidebar
        $accessibleModules = $user->getAccessibleModules();

        // Build permissions map for each module
        $modulePermissions = [];
        foreach ($accessibleModules as $module) {
            $modulePermissions[$module->slug] = $user->getModulePermissions($module->slug);
        }

        $view->with('accessibleModules', $accessibleModules);
        $view->with('modulePermissions', $modulePermissions);
    }
}
