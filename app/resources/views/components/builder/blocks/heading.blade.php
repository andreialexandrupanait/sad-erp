@props(['isTemplate' => false])

<div class="px-4 py-2">
    <input type="text" x-model="block.data.content"
           :class="{
               'text-3xl': block.data.level === 'h1',
               'text-2xl': block.data.level === 'h2',
               'text-xl': block.data.level === 'h3' || !block.data.level
           }"
           class="w-full font-bold text-slate-900 bg-transparent border-none focus:ring-0 p-0"
           placeholder="{{ __('Enter heading...') }}">
</div>
