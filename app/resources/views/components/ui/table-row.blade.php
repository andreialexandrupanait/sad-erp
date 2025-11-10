<tr {{ $attributes->merge(['class' => 'border-b transition-colors hover:bg-slate-50/50 data-[state=selected]:bg-slate-100']) }}>
    {{ $slot }}
</tr>
