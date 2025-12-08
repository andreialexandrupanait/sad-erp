<x-app-layout>
    <x-slot name="pageTitle">Test</x-slot>
    @php
        $testFilters = ['status' => [], 'q' => '', 'sort' => 'name:asc', 'page' => 1];
    @endphp
    <div x-data="{ filters: @js($testFilters) }">
        Test content
    </div>
</x-app-layout>