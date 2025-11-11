<x-app-layout>
    <x-slot name="pageTitle">Cheltuieli</x-slot>

    <x-slot name="headerActions">
        <button @click="$dispatch('open-slide-panel', 'expense-create')" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 text-sm font-medium transition-colors">
            + Adaugă cheltuială
        </button>
    </x-slot>

    <div class="p-6" x-data>

        @if(session('success'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-700">
                {{ session('success') }}
            </div>
        @endif

        <!-- Filters -->
        <form method="GET" class="mb-6 flex gap-2 flex-wrap">
            <select name="year" class="rounded-lg border-slate-300">
                @foreach($availableYears as $availableYear)
                    <option value="{{ $availableYear }}" {{ $year == $availableYear ? 'selected' : '' }}>{{ $availableYear }}</option>
                @endforeach
            </select>
            <select name="month" class="rounded-lg border-slate-300">
                <option value="">Toate lunile</option>
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                    </option>
                @endfor
            </select>
            <select name="currency" class="rounded-lg border-slate-300">
                <option value="">Toate valutele</option>
                <option value="RON" {{ $currency == 'RON' ? 'selected' : '' }}>RON</option>
                <option value="EUR" {{ $currency == 'EUR' ? 'selected' : '' }}>EUR</option>
            </select>
            <select name="category_id" class="rounded-lg border-slate-300">
                <option value="">Toate categoriile</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ $categoryId == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-slate-600 text-white rounded-lg hover:bg-slate-700">Filtrează</button>
        </form>

        <!-- Totals -->
        <div class="mb-4 flex gap-4">
            @foreach($totals as $curr => $total)
                <div class="px-4 py-2 bg-red-50 rounded-lg">
                    <span class="text-sm text-slate-600">Total {{ $curr }}:</span>
                    <span class="ml-2 font-bold text-red-700">{{ number_format($total, 2) }}</span>
                </div>
            @endforeach
        </div>

        <!-- Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Dată</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Document</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Categorie</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Sumă</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase">Acțiuni</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($expenses as $expense)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $expense->occurred_at->format('d M Y') }}</td>
                            <td class="px-6 py-4 text-sm font-medium">{{ $expense->document_name }}</td>
                            <td class="px-6 py-4 text-sm">
                                @if($expense->category)
                                    <span class="px-2 py-1 rounded text-xs {{ $expense->category->badge_class }}">
                                        {{ $expense->category->name }}
                                    </span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm font-bold text-red-600">{{ number_format($expense->amount, 2) }} {{ $expense->currency }}</td>
                            <td class="px-6 py-4 text-right text-sm space-x-2">
                                <button @click="$dispatch('open-slide-panel', 'expense-edit-{{ $expense->id }}')" class="text-blue-600 hover:text-blue-900">
                                    Editează
                                </button>
                                <form method="POST" action="{{ route('financial.expenses.destroy', $expense) }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Ești sigur?')" class="text-red-600 hover:text-red-900">Șterge</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-slate-500">Nu există cheltuieli</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $expenses->links() }}
        </div>
    </div>

    <!-- Toast Notifications -->
    <x-toast />

    <!-- Create Expense Slide Panel -->
    <x-slide-panel name="expense-create" :show="false" maxWidth="2xl">
        <div class="flex items-center justify-between px-8 py-6 border-b border-slate-200">
            <h2 class="text-2xl font-bold text-slate-900">Adaugă cheltuială nouă</h2>
            <button type="button" @click="$dispatch('close-slide-panel', 'expense-create')" class="text-slate-400 hover:text-slate-600 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto px-8 py-6">
            <form id="expense-create-form" x-data="{loading:false,async submit(e){e.preventDefault();this.loading=true;document.querySelectorAll('#expense-create-form .error-message').forEach(el=>el.remove());const fd=new FormData(e.target);try{const r=await fetch('{{route('financial.expenses.store')}}',{method:'POST',headers:{'X-CSRF-TOKEN':'{{csrf_token()}}','Accept':'application/json'},body:fd});const d=await r.json();if(r.ok){$dispatch('close-slide-panel','expense-create');$dispatch('toast',{message:'Expense created successfully!',type:'success'});setTimeout(()=>window.location.reload(),500);}else if(d.errors){Object.keys(d.errors).forEach(k=>{const i=document.querySelector(`#expense-create-form [name='${k}']`);if(i){const w=i.closest('.field-wrapper');if(w){const existingError=w.querySelector('.error-message');if(existingError)existingError.remove();const err=document.createElement('p');err.className='error-message mt-2 text-sm text-red-600';err.textContent=d.errors[k][0];w.appendChild(err);}}});$dispatch('toast',{message:'Please correct the errors.',type:'error'});}}catch(err){console.error(err);$dispatch('toast',{message:'An error occurred.',type:'error'});}finally{this.loading=false;}}}" @submit="submit">
                @csrf
                <div class="grid grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-6">
                    <div class="sm:col-span-6 field-wrapper">
                        <x-ui.label for="expense_document_name_create">Nume document <span class="text-red-500">*</span></x-ui.label>
                        <div class="mt-2">
                            <x-ui.input type="text" name="document_name" id="expense_document_name_create" required/>
                        </div>
                    </div>

                    <div class="sm:col-span-3 field-wrapper">
                        <x-ui.label for="expense_amount_create">Sumă <span class="text-red-500">*</span></x-ui.label>
                        <div class="mt-2">
                            <x-ui.input type="number" step="0.01" name="amount" id="expense_amount_create" required/>
                        </div>
                    </div>

                    <div class="sm:col-span-3 field-wrapper">
                        <x-ui.label for="expense_currency_create">Valută <span class="text-red-500">*</span></x-ui.label>
                        <div class="mt-2">
                            <x-ui.select name="currency" id="expense_currency_create" required>
                                <option value="RON">RON</option>
                                <option value="EUR">EUR</option>
                            </x-ui.select>
                        </div>
                    </div>

                    <div class="sm:col-span-3 field-wrapper">
                        <x-ui.label for="expense_occurred_at_create">Dată <span class="text-red-500">*</span></x-ui.label>
                        <div class="mt-2">
                            <x-ui.input type="date" name="occurred_at" id="expense_occurred_at_create" value="{{now()->format('Y-m-d')}}" required/>
                        </div>
                    </div>

                    <div class="sm:col-span-3 field-wrapper">
                        <x-ui.label for="expense_category_create">Categorie</x-ui.label>
                        <div class="mt-2">
                            <x-ui.select name="category_option_id" id="expense_category_create">
                                <option value="">Selectează categorie (opțional)</option>
                                @foreach($categories as $category)
                                    <option value="{{$category->id}}">{{$category->name}}</option>
                                @endforeach
                            </x-ui.select>
                        </div>
                    </div>

                    <div class="sm:col-span-6 field-wrapper">
                        <x-ui.label for="expense_note_create">Notă</x-ui.label>
                        <div class="mt-2">
                            <x-ui.textarea name="note" id="expense_note_create" rows="3"></x-ui.textarea>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="flex items-center justify-end gap-x-3 px-8 py-6 border-t border-slate-200 bg-slate-50">
            <x-ui.button type="button" variant="ghost" @click="$dispatch('close-slide-panel','expense-create')">Anulează</x-ui.button>
            <x-ui.button type="submit" form="expense-create-form" variant="default">Salvează cheltuială</x-ui.button>
        </div>
    </x-slide-panel>

    @foreach($expenses as $expense)
    <x-slide-panel name="expense-edit-{{$expense->id}}" :show="false" maxWidth="2xl">
        <div class="flex items-center justify-between px-8 py-6 border-b border-slate-200">
            <h2 class="text-2xl font-bold text-slate-900">Editează cheltuială</h2>
            <button type="button" @click="$dispatch('close-slide-panel','expense-edit-{{$expense->id}}')" class="text-slate-400 hover:text-slate-600 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto px-8 py-6">
            <form id="expense-edit-form-{{$expense->id}}" x-data="{loading:false,async submit(e){e.preventDefault();this.loading=true;document.querySelectorAll('#expense-edit-form-{{$expense->id}} .error-message').forEach(el=>el.remove());const fd=new FormData(e.target);try{const r=await fetch('{{route('financial.expenses.update',$expense)}}',{method:'POST',headers:{'X-CSRF-TOKEN':'{{csrf_token()}}','Accept':'application/json'},body:fd});const d=await r.json();if(r.ok){$dispatch('close-slide-panel','expense-edit-{{$expense->id}}');$dispatch('toast',{message:'Expense updated!',type:'success'});setTimeout(()=>window.location.reload(),500);}else if(d.errors){Object.keys(d.errors).forEach(k=>{const i=document.querySelector(`#expense-edit-form-{{$expense->id}} [name='${k}']`);if(i){const w=i.closest('.field-wrapper');if(w){const existingError=w.querySelector('.error-message');if(existingError)existingError.remove();const err=document.createElement('p');err.className='error-message mt-2 text-sm text-red-600';err.textContent=d.errors[k][0];w.appendChild(err);}}});$dispatch('toast',{message:'Please correct errors.',type:'error'});}}catch(err){console.error(err);$dispatch('toast',{message:'Error occurred.',type:'error'});}finally{this.loading=false;}}}" @submit="submit">
                @csrf @method('PUT')
                <div class="grid grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-6">
                    <div class="sm:col-span-6 field-wrapper">
                        <x-ui.label for="expense_document_name_edit_{{$expense->id}}">Nume document <span class="text-red-500">*</span></x-ui.label>
                        <div class="mt-2">
                            <x-ui.input type="text" name="document_name" id="expense_document_name_edit_{{$expense->id}}" value="{{$expense->document_name}}" required/>
                        </div>
                    </div>

                    <div class="sm:col-span-3 field-wrapper">
                        <x-ui.label for="expense_amount_edit_{{$expense->id}}">Sumă <span class="text-red-500">*</span></x-ui.label>
                        <div class="mt-2">
                            <x-ui.input type="number" step="0.01" name="amount" id="expense_amount_edit_{{$expense->id}}" value="{{$expense->amount}}" required/>
                        </div>
                    </div>

                    <div class="sm:col-span-3 field-wrapper">
                        <x-ui.label for="expense_currency_edit_{{$expense->id}}">Valută <span class="text-red-500">*</span></x-ui.label>
                        <div class="mt-2">
                            <x-ui.select name="currency" id="expense_currency_edit_{{$expense->id}}" required>
                                <option value="RON" {{$expense->currency=='RON'?'selected':''}}>RON</option>
                                <option value="EUR" {{$expense->currency=='EUR'?'selected':''}}>EUR</option>
                            </x-ui.select>
                        </div>
                    </div>

                    <div class="sm:col-span-3 field-wrapper">
                        <x-ui.label for="expense_occurred_at_edit_{{$expense->id}}">Dată <span class="text-red-500">*</span></x-ui.label>
                        <div class="mt-2">
                            <x-ui.input type="date" name="occurred_at" id="expense_occurred_at_edit_{{$expense->id}}" value="{{$expense->occurred_at->format('Y-m-d')}}" required/>
                        </div>
                    </div>

                    <div class="sm:col-span-3 field-wrapper">
                        <x-ui.label for="expense_category_edit_{{$expense->id}}">Categorie</x-ui.label>
                        <div class="mt-2">
                            <x-ui.select name="category_option_id" id="expense_category_edit_{{$expense->id}}">
                                <option value="">Selectează categorie (opțional)</option>
                                @foreach($categories as $category)
                                    <option value="{{$category->id}}" {{$expense->category_option_id==$category->id?'selected':''}}>{{$category->name}}</option>
                                @endforeach
                            </x-ui.select>
                        </div>
                    </div>

                    <div class="sm:col-span-6 field-wrapper">
                        <x-ui.label for="expense_note_edit_{{$expense->id}}">Notă</x-ui.label>
                        <div class="mt-2">
                            <x-ui.textarea name="note" id="expense_note_edit_{{$expense->id}}" rows="3">{{$expense->note}}</x-ui.textarea>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="flex items-center justify-end gap-x-3 px-8 py-6 border-t border-slate-200 bg-slate-50">
            <x-ui.button type="button" variant="ghost" @click="$dispatch('close-slide-panel','expense-edit-{{$expense->id}}')">Anulează</x-ui.button>
            <x-ui.button type="submit" form="expense-edit-form-{{$expense->id}}" variant="default">Actualizează cheltuială</x-ui.button>
        </div>
    </x-slide-panel>
    @endforeach
</x-app-layout>
