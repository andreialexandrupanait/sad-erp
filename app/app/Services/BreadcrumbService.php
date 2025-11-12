<?php

namespace App\Services;

use Illuminate\Support\Facades\Route;

class BreadcrumbService
{
    /**
     * Route name to label mapping
     */
    protected static array $routeLabels = [
        'dashboard' => 'Dashboard',

        // Clients
        'clients.index' => 'Clienți',
        'clients.create' => 'Client nou',
        'clients.show' => 'Detalii client',
        'clients.edit' => 'Editează client',

        // Subscriptions
        'subscriptions.index' => 'Abonamente',
        'subscriptions.create' => 'Abonament nou',
        'subscriptions.show' => 'Detalii abonament',
        'subscriptions.edit' => 'Editează abonament',

        // Credentials
        'credentials.index' => 'Acces & parole',
        'credentials.create' => 'Acces nou',
        'credentials.show' => 'Detalii acces',
        'credentials.edit' => 'Editează acces',

        // Domains
        'domains.index' => 'Domenii',
        'domains.create' => 'Domeniu nou',
        'domains.show' => 'Detalii domeniu',
        'domains.edit' => 'Editează domeniu',

        // Internal Accounts
        'internal-accounts.index' => 'Conturi Interne',
        'internal-accounts.create' => 'Cont nou',
        'internal-accounts.show' => 'Detalii cont',
        'internal-accounts.edit' => 'Editează cont',

        // Financial
        'financial.dashboard' => 'Financiar',
        'financial.revenues.index' => 'Venituri',
        'financial.revenues.create' => 'Venit nou',
        'financial.revenues.edit' => 'Editează venit',
        'financial.expenses.index' => 'Cheltuieli',
        'financial.expenses.create' => 'Cheltuială nouă',
        'financial.expenses.edit' => 'Editează cheltuială',

        // Settings
        'settings.index' => 'Setări',
        'settings.client-statuses' => 'Status clienti',
        'settings.domain-statuses' => 'Status domenii',
        'settings.subscription-statuses' => 'Status abonamente',
        'settings.access-platforms' => 'Categorii platforme',
        'settings.expense-categories' => 'Categorii cheltuieli',
        'settings.payment-methods' => 'Metode de plata',
        'settings.billing-cycles' => 'Cicluri de facturare',
        'settings.domain-registrars' => 'Registratori de domenii',
        'profile.edit' => 'Profil',
    ];

    /**
     * Generate breadcrumbs from current route
     */
    public static function generate(?string $routeName = null): array
    {
        $routeName = $routeName ?? Route::currentRouteName();

        if (!$routeName) {
            return [['label' => 'Dashboard', 'url' => route('dashboard')]];
        }

        // If it's the dashboard, return just dashboard
        if ($routeName === 'dashboard') {
            return [['label' => 'Dashboard', 'url' => route('dashboard')]];
        }

        $breadcrumbs = [];

        // Always start with Dashboard
        $breadcrumbs[] = ['label' => 'Dashboard', 'url' => route('dashboard')];

        // Parse route segments
        $segments = explode('.', $routeName);
        $currentPath = '';

        foreach ($segments as $index => $segment) {
            $currentPath .= ($currentPath ? '.' : '') . $segment;

            // Skip 'create', 'edit', 'show' for intermediate breadcrumbs
            if (in_array($segment, ['create', 'edit', 'show', 'destroy']) && $index < count($segments) - 1) {
                continue;
            }

            $isLastSegment = ($index === count($segments) - 1);

            // Special handling for hierarchical routes (settings and financial sections)
            // Only apply for routes like settings.client-statuses or financial.revenues.index
            if ($index === 0 && !$isLastSegment && count($segments) >= 2) {
                $secondSegment = $segments[1] ?? '';

                // Check if this is a settings or financial route with a non-index child
                // Skip if the second segment is 'index' or 'dashboard' (those are the parent pages themselves)
                if (in_array($currentPath, ['settings', 'financial']) &&
                    !in_array($secondSegment, ['index', 'dashboard'])) {

                    // Check for parent routes
                    $possibleParentIndex = $currentPath . '.index';
                    $possibleParentDashboard = $currentPath . '.dashboard';

                    // Add parent breadcrumb if it exists
                    if (Route::has($possibleParentIndex)) {
                        $label = self::$routeLabels[$possibleParentIndex] ?? ucfirst($segment);
                        try {
                            $breadcrumbs[] = ['label' => $label, 'url' => route($possibleParentIndex)];
                        } catch (\Exception $e) {
                            $breadcrumbs[] = ['label' => $label, 'url' => '#'];
                        }
                        continue; // Skip normal processing for this segment
                    } elseif (Route::has($possibleParentDashboard)) {
                        $label = self::$routeLabels[$possibleParentDashboard] ?? ucfirst($segment);
                        try {
                            $breadcrumbs[] = ['label' => $label, 'url' => route($possibleParentDashboard)];
                        } catch (\Exception $e) {
                            $breadcrumbs[] = ['label' => $label, 'url' => '#'];
                        }
                        continue; // Skip normal processing for this segment
                    }
                }
            }

            // Check if we have a route for this path
            if (Route::has($currentPath)) {
                $label = self::$routeLabels[$currentPath] ?? ucfirst($segment);

                try {
                    // For the last segment, always add it (current page)
                    if ($isLastSegment) {
                        $breadcrumbs[] = ['label' => $label, 'url' => route($currentPath)];
                    } else {
                        // Add intermediate if it's an index route or has no dots
                        if (str_ends_with($currentPath, '.index') || !str_contains($currentPath, '.')) {
                            $breadcrumbs[] = ['label' => $label, 'url' => route($currentPath)];
                        }
                    }
                } catch (\Exception $e) {
                    // If route requires parameters, just add label without URL
                    $breadcrumbs[] = ['label' => $label, 'url' => '#'];
                }
            }
        }

        return $breadcrumbs;
    }

    /**
     * Get label for a specific route
     */
    public static function getLabel(string $routeName): string
    {
        return self::$routeLabels[$routeName] ?? ucfirst(last(explode('.', $routeName)));
    }

    /**
     * Add custom route label
     */
    public static function addLabel(string $routeName, string $label): void
    {
        self::$routeLabels[$routeName] = $label;
    }
}
