@props(['subscription' => null, 'action', 'method' => 'POST'])

<form method="POST" action="{{ $action }}" class="space-y-6" x-data="{ billingCycle: '{{ old('billing_cycle', $subscription->billing_cycle ?? 'monthly') }}' }">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
        <div class="px-4 py-6 sm:p-8">
            <x-subscription-form-fields :subscription="$subscription" />
        </div>

        <div class="flex items-center justify-end gap-x-6 border-t border-gray-900/10 px-4 py-4 sm:px-8">
            <a href="{{ route('subscriptions.index') }}" class="text-sm font-semibold leading-6 text-gray-900">Cancel</a>
            <button type="submit" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                {{ $subscription ? 'Update Subscription' : 'Create Subscription' }}
            </button>
        </div>
    </div>
</form>
