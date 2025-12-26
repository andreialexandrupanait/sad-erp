@props(['isTemplate' => false])

<div class="px-4 py-4">
    <div class="border-l-4 border-blue-400 bg-blue-50 p-5 rounded-r-lg">
        <textarea x-model="block.data.content" rows="3" placeholder="{{ __('Enter quote text...') }}"
                  class="w-full text-slate-700 italic bg-transparent border-none focus:ring-0 p-0 resize-none"></textarea>
        <input type="text" x-model="block.data.author" placeholder="{{ __('Quote author (optional)') }}"
               class="mt-2 text-sm text-blue-600 bg-transparent border-none focus:ring-0 p-0 w-full">
    </div>
</div>
