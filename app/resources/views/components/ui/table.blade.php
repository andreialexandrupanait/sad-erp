@props(['headers' => []])

<div {{ $attributes->merge(['class' => 'relative w-full overflow-auto']) }}>
    <table class="w-full caption-bottom text-sm">
        @if(!empty($headers))
            <thead class="[&_tr]:border-b">
                <tr class="border-b transition-colors hover:bg-slate-50/50">
                    @foreach($headers as $header)
                        <th class="h-12 px-4 text-left align-middle font-medium text-slate-500 [&:has([role=checkbox])]:pr-0">
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
