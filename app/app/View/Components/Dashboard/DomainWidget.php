<?php

namespace App\View\Components\Dashboard;

use Illuminate\View\Component;
use Illuminate\Support\Collection;

class DomainWidget extends Component
{
    public Collection $expiringDomains;
    public array $domainRenewals30Days;
    public array $domainRenewals60Days;
    public array $domainRenewals90Days;
    public array $processedDomains;

    /**
     * Create a new component instance.
     *
     * @param Collection $expiringDomains Domains expiring soon
     * @param array $domainRenewals30Days Renewal data for 30 days
     * @param array $domainRenewals60Days Renewal data for 60 days
     * @param array $domainRenewals90Days Renewal data for 90 days
     */
    public function __construct(
        Collection $expiringDomains,
        array $domainRenewals30Days,
        array $domainRenewals60Days,
        array $domainRenewals90Days
    ) {
        $this->expiringDomains = $expiringDomains;
        $this->domainRenewals30Days = $domainRenewals30Days;
        $this->domainRenewals60Days = $domainRenewals60Days;
        $this->domainRenewals90Days = $domainRenewals90Days;

        // Pre-process domain expiry data
        $this->processedDomains = $expiringDomains->take(3)->map(function($domain) {
            $daysUntilExpiry = $this->getDaysUntilExpiry($domain->expiry_date);
            return [
                'domain' => $domain,
                'daysUntilExpiry' => $daysUntilExpiry,
                'isPast' => $this->isPast($daysUntilExpiry),
                'daysText' => abs($daysUntilExpiry),
                'dayLabel' => $this->getDayLabel(abs($daysUntilExpiry)),
            ];
        })->toArray();
    }

    /**
     * Calculate days until expiry for a domain.
     *
     * @param \Carbon\Carbon $expiryDate The domain expiry date
     * @return int Days until expiry (negative if expired)
     */
    public function getDaysUntilExpiry($expiryDate): int
    {
        return now()->startOfDay()->diffInDays($expiryDate->startOfDay(), false);
    }

    /**
     * Check if a domain is past its expiry date.
     *
     * @param int $daysUntilExpiry Days until expiry
     * @return bool True if expired
     */
    public function isPast(int $daysUntilExpiry): bool
    {
        return $daysUntilExpiry < 0;
    }

    /**
     * Get the singular or plural form of "day"/"days".
     *
     * @param int $count Number of days
     * @return string Translated "day" or "days"
     */
    public function getDayLabel(int $count): string
    {
        return $count == 1 ? __('app.day') : __('app.days');
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.dashboard.domain-widget');
    }
}
