@props([
    'disabled' => false,
])

<select
    {{ $disabled ? 'disabled' : '' }}
    {{ $attributes->merge(['class' => 'appearance-none flex h-10 w-full items-center justify-between rounded-md border border-slate-200 bg-white pl-3 pr-10 py-2 text-sm ring-offset-white placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-950 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 bg-[url(\'data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2020%2020%22%3E%3Cpath%20stroke%3D%22%236b7280%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20stroke-width%3D%221.5%22%20d%3D%22m6%208%204%204%204-4%22%2F%3E%3C%2Fsvg%3E\')] bg-[length:1.25rem_1.25rem] bg-[right_0.75rem_center] bg-no-repeat']) }}
>
    {{ $slot }}
</select>
