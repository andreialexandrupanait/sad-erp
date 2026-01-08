@props(['isTemplate' => false])

<div class="px-4 py-4">
    <input type="text" x-model="block.data.title" placeholder="{{ __('Table Title (optional)') }}"
           class="w-full text-lg font-semibold text-slate-900 bg-transparent border-none focus:ring-0 p-0 mb-3">
    <div class="overflow-x-auto">
        <table class="w-full border-collapse border border-slate-200">
            <thead>
                <tr>
                    <template x-for="(col, colIndex) in block.data.columns" :key="'col-'+colIndex">
                        <th class="border border-slate-200 bg-slate-50 p-2">
                            <input type="text" :value="col" @input="updateTableColumn(block, colIndex, $event.target.value)"
                                   class="w-full text-center text-sm font-medium bg-transparent border-none focus:ring-0 p-0">
                        </th>
                    </template>
                    <th class="border border-slate-200 bg-slate-50 w-10">
                        <button type="button" @click="addTableColumn(block)" class="text-slate-400 hover:text-slate-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                        </button>
                    </th>
                </tr>
            </thead>
            <tbody>
                <template x-for="(row, rowIndex) in block.data.rows" :key="'row-'+rowIndex">
                    <tr>
                        <template x-for="(cell, cellIndex) in row" :key="'cell-'+rowIndex+'-'+cellIndex">
                            <td class="border border-slate-200 p-2">
                                <input type="text" :value="cell" @input="updateTableCell(block, rowIndex, cellIndex, $event.target.value)"
                                       class="w-full text-sm bg-transparent border-none focus:ring-0 p-0">
                            </td>
                        </template>
                        <td class="border border-slate-200 w-10">
                            <button type="button" @click="removeTableRow(block, rowIndex)" class="text-slate-400 hover:text-red-600 p-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
    <button type="button" @click="addTableRow(block)" class="mt-2 text-sm text-slate-500 hover:text-slate-700 flex items-center gap-1">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        {{ __('Add Row') }}
    </button>
</div>
