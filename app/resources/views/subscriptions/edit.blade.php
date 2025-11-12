<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Subscription') }}: {{ $subscription->vendor_name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-subscription-form
                :subscription="$subscription"
                :action="route('subscriptions.update', $subscription)"
                method="PATCH"
                :billingCycles="$billingCycles"
                :statuses="$statuses"
            />
        </div>
    </div>
</x-app-layout>
