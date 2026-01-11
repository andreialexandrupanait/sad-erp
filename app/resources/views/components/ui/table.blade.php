@props(['headers' => []])

<div {{ $attributes->merge(['class' => 'relative w-full overflow-auto']) }}>
    <table class="w-full caption-bottom text-sm">
        @if(!empty($headers))
            <thead class="bg-slate-100">
                <tr class="border-b border-slate-200">
                    @foreach($headers as $header)
                        <th class="px-3 md:px-6 py-3 md:py-4 text-left align-middle font-medium text-slate-600 [&:has([role=checkbox])]:pr-0">
                            {{ $header }}
                        </th>
                    @endforeach
                </tr>
            </thead>
        @endif
        <tbody class="[&_tr:last-child]:border-0">
            {{ $slot }}
        </tbody>
    </table>
</div>
