<x-app-layout>
    <x-slot name="pageTitle">Conturi Interne</x-slot>

    <x-slot name="headerActions">
        <x-ui.button variant="default" @click="$dispatch('open-slide-panel', 'internal-account-create')">
            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Cont nou
        </x-ui.button>
    </x-slot>

    <div class="p-6 space-y-6" x-data>
        <!-- Success/Info Messages -->
        @if (session('success'))
            <x-ui.alert variant="success">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>{{ session('success') }}</div>
            </x-ui.alert>
        @endif

        <!-- Statistics Cards -->
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
            <!-- Total Accounts - Featured -->
            <div class="rounded-lg border border-slate-200 bg-gradient-to-br from-slate-900 to-slate-800 text-white shadow-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-slate-300">Total Accounts</p>
                            <p class="mt-2 text-3xl font-bold">{{ $stats['total_accounts'] }}</p>
                            <p class="mt-1 text-xs text-slate-400">all accounts</p>
                        </div>
                        <div class="ml-4 flex h-12 w-12 items-center justify-center rounded-lg bg-white/10">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- My Accounts -->
            <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-slate-600">My Accounts</p>
                            <p class="mt-2 text-2xl font-bold text-slate-900">{{ $stats['my_accounts'] }}</p>
                            <p class="mt-1 text-xs text-slate-500">owned by me</p>
                        </div>
                        <div class="ml-4 flex h-12 w-12 items-center justify-center rounded-lg bg-blue-50">
                            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Team Shared -->
            <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-slate-600">Team Shared</p>
                            <p class="mt-2 text-2xl font-bold text-slate-900">{{ $stats['team_accounts'] }}</p>
                            <p class="mt-1 text-xs text-slate-500">accessible to team</p>
                        </div>
                        <div class="ml-4 flex h-12 w-12 items-center justify-center rounded-lg bg-green-50">
                            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Unique Platforms -->
            <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-slate-600">Unique Platforms</p>
                            <p class="mt-2 text-2xl font-bold text-slate-900">{{ $stats['unique_platforms'] }}</p>
                            <p class="mt-1 text-xs text-slate-500">different platforms</p>
                        </div>
                        <div class="ml-4 flex h-12 w-12 items-center justify-center rounded-lg bg-purple-50">
                            <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and Filters -->
        <x-ui.card>
            <x-ui.card-content>
                <form method="GET" action="{{ route('internal-accounts.index') }}">
                    <div class="flex flex-col sm:flex-row gap-3">
                        <!-- Search -->
                        <div class="flex-1">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                </div>
                                <x-ui.input
                                    type="text"
                                    name="search"
                                    value="{{ request('search') }}"
                                    placeholder="Search by account name, platform..."
                                    class="pl-10"
                                />
                            </div>
                        </div>

                        <!-- Platform Filter -->
                        <div class="w-full sm:w-48">
                            <x-ui.select name="platform">
                                <option value="">All Platforms</option>
                                @foreach ($platforms as $key => $value)
                                    <option value="{{ $key }}" {{ request('platform') == $key ? 'selected' : '' }}>
                                        {{ $value }}
                                    </option>
                                @endforeach
                            </x-ui.select>
                        </div>

                        <!-- Ownership Filter -->
                        <div class="w-full sm:w-44">
                            <x-ui.select name="ownership">
                                <option value="">All Accounts</option>
                                <option value="mine" {{ request('ownership') == 'mine' ? 'selected' : '' }}>My Accounts Only</option>
                                <option value="team" {{ request('ownership') == 'team' ? 'selected' : '' }}>Team Shared Only</option>
                            </x-ui.select>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex gap-2">
                            <x-ui.button type="submit" variant="default">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                                </svg>
                                Search
                            </x-ui.button>
                        </div>
                    </div>
                </form>
            </x-ui.card-content>
        </x-ui.card>

        <!-- Accounts Table -->
        <x-ui.card>
            @if ($accounts->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full caption-bottom text-sm">
                        <thead class="[&_tr]:border-b">
                            <tr class="border-b transition-colors hover:bg-slate-50/50">
                                <x-ui.table-head>
                                    <a href="{{ route('internal-accounts.index', array_merge(request()->all(), ['sort' => 'nume_cont_aplicatie', 'dir' => request('sort') == 'nume_cont_aplicatie' && request('dir') == 'asc' ? 'desc' : 'asc'])) }}" class="hover:underline">
                                        Account Name
                                    </a>
                                </x-ui.table-head>
                                <x-ui.table-head>
                                    <a href="{{ route('internal-accounts.index', array_merge(request()->all(), ['sort' => 'platforma', 'dir' => request('sort') == 'platforma' && request('dir') == 'asc' ? 'desc' : 'asc'])) }}" class="hover:underline">
                                        Platform
                                    </a>
                                </x-ui.table-head>
                                <x-ui.table-head>Username</x-ui.table-head>
                                <x-ui.table-head>Password</x-ui.table-head>
                                <x-ui.table-head>Access</x-ui.table-head>
                                <x-ui.table-head>Owner</x-ui.table-head>
                                <x-ui.table-head class="text-right">Actions</x-ui.table-head>
                            </tr>
                        </thead>
                        <tbody class="[&_tr:last-child]:border-0">
                            @foreach ($accounts as $account)
                                <x-ui.table-row>
                                    <x-ui.table-cell>
                                        <div class="text-sm font-medium text-slate-900">
                                            {{ $account->nume_cont_aplicatie }}
                                        </div>
                                    </x-ui.table-cell>
                                    <x-ui.table-cell>
                                        <div class="text-sm text-slate-700">
                                            {{ $account->platforma }}
                                        </div>
                                    </x-ui.table-cell>
                                    <x-ui.table-cell>
                                        <div class="text-sm text-slate-700">
                                            {{ $account->username ?? '-' }}
                                        </div>
                                    </x-ui.table-cell>
                                    <x-ui.table-cell>
                                        <div class="text-sm text-slate-500 font-mono">
                                            {{ $account->masked_password }}
                                        </div>
                                    </x-ui.table-cell>
                                    <x-ui.table-cell>
                                        @if ($account->accesibil_echipei)
                                            <x-ui.badge variant="success">
                                                Team
                                            </x-ui.badge>
                                        @else
                                            <x-ui.badge variant="secondary">
                                                Private
                                            </x-ui.badge>
                                        @endif
                                    </x-ui.table-cell>
                                    <x-ui.table-cell>
                                        <div class="text-sm text-slate-600">
                                            {{ $account->isOwner() ? 'You' : $account->user->name }}
                                        </div>
                                    </x-ui.table-cell>
                                    <x-ui.table-cell class="text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <x-ui.button
                                                variant="secondary"
                                                size="sm"
                                                onclick="window.location.href='{{ route('internal-accounts.show', $account) }}'"
                                            >
                                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                                View
                                            </x-ui.button>
                                            @if ($account->isOwner())
                                                <x-ui.button
                                                    variant="outline"
                                                    size="sm"
                                                    @click="$dispatch('open-slide-panel', 'internal-account-edit-{{ $account->id }}')"
                                                >
                                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                    Edit
                                                </x-ui.button>
                                                <form action="{{ route('internal-accounts.destroy', $account) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this account?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <x-ui.button type="submit" variant="destructive" size="sm">
                                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                        </svg>
                                                        Delete
                                                    </x-ui.button>
                                                </form>
                                            @else
                                                <span class="text-sm text-slate-400">View Only</span>
                                            @endif
                                        </div>
                                    </x-ui.table-cell>
                                </x-ui.table-row>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($accounts->hasPages())
                    <div class="bg-slate-50 px-6 py-4 border-t border-slate-200">
                        {{ $accounts->links() }}
                    </div>
                @endif
            @else
                <div class="px-6 py-16 text-center">
                    <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-slate-900">No internal accounts found</h3>
                    <p class="mt-1 text-sm text-slate-500">Get started by creating your first internal account.</p>
                    <div class="mt-6">
                        <x-ui.button variant="default" onclick="window.location.href='{{ route('internal-accounts.create') }}'">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Add Your First Account
                        </x-ui.button>
                    </div>
                </div>
            @endif
        </x-ui.card>
    </div>

    <!-- Toast Notifications -->
    <x-toast />

    <!-- Create Internal Account Slide Panel -->
    <x-slide-panel name="internal-account-create" :show="false" maxWidth="2xl">
        <div class="flex items-center justify-between px-8 py-6 border-b border-slate-200">
            <h2 class="text-2xl font-bold text-slate-900">New Internal Account</h2>
            <button type="button" @click="$dispatch('close-slide-panel', 'internal-account-create')" class="text-slate-400 hover:text-slate-600 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto px-8 py-6">
            <form id="internal-account-create-form" x-data="{loading:false,showPass:false,async submit(e){e.preventDefault();this.loading=true;document.querySelectorAll('#internal-account-create-form .error-message').forEach(el=>el.remove());const fd=new FormData(e.target);try{const r=await fetch('{{route('internal-accounts.store')}}',{method:'POST',headers:{'X-CSRF-TOKEN':'{{csrf_token()}}','Accept':'application/json'},body:fd});const d=await r.json();if(r.ok){$dispatch('close-slide-panel','internal-account-create');$dispatch('toast',{message:'Internal account created successfully!',type:'success'});setTimeout(()=>window.location.reload(),500);}else if(d.errors){Object.keys(d.errors).forEach(k=>{const i=document.querySelector(`#internal-account-create-form [name='${k}']`);if(i){const w=i.closest('div');const err=document.createElement('p');err.className='error-message mt-2 text-sm text-red-600';err.textContent=d.errors[k][0];w.appendChild(err);}});$dispatch('toast',{message:'Please correct the errors.',type:'error'});}}catch(err){console.error(err);$dispatch('toast',{message:'An error occurred.',type:'error'});}finally{this.loading=false;}}}\" @submit="submit">
                @csrf
                <div class="grid grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-6">
                    <div class="sm:col-span-6 field-wrapper">
                        <x-ui.label for="nume_cont_aplicatie">Account / Application Name <span class="text-red-500">*</span></x-ui.label>
                        <div class="mt-2">
                            <x-ui.input type="text" name="nume_cont_aplicatie" id="nume_cont_aplicatie" required placeholder="e.g., Company Bank Account, AWS Root"/>
                        </div>
                    </div>

                    <div class="sm:col-span-6 field-wrapper">
                        <x-ui.label for="platforma">Platform <span class="text-red-500">*</span></x-ui.label>
                        <div class="mt-2">
                            <x-ui.input
                                type="text"
                                name="platforma"
                                id="platforma"
                                required
                                placeholder="e.g., Bank Account, CRM System, Email Service"
                            />
                        </div>
                    </div>

                    <div class="sm:col-span-3 field-wrapper">
                        <x-ui.label for="url">URL</x-ui.label>
                        <div class="mt-2">
                            <x-ui.input type="url" name="url" id="url" placeholder="https://example.com/login"/>
                        </div>
                    </div>

                    <div class="sm:col-span-3 field-wrapper">
                        <x-ui.label for="username">Username / Email</x-ui.label>
                        <div class="mt-2">
                            <x-ui.input type="text" name="username" id="username" placeholder="admin or email@company.com"/>
                        </div>
                    </div>

                    <div class="sm:col-span-6 field-wrapper">
                        <x-ui.label for="password">Password</x-ui.label>
                        <div class="mt-2 relative">
                            <input x-bind:type="showPass?'text':'password'" name="password" id="password" placeholder="Enter password" class="pr-10 block w-full rounded-lg border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900 sm:text-sm"/>
                            <button type="button" @click="showPass=!showPass" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-slate-500 hover:text-slate-700">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="sm:col-span-6 field-wrapper">
                        <div class="flex items-start">
                            <div class="flex h-6 items-center">
                                <input id="accesibil_echipei_create" name="accesibil_echipei" type="checkbox" value="1" class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900">
                            </div>
                            <div class="ml-3 text-sm leading-6">
                                <label for="accesibil_echipei_create" class="font-medium text-slate-900">Make accessible to team</label>
                                <p class="text-slate-500">Allow all team members to view this account (you remain the owner)</p>
                            </div>
                        </div>
                    </div>

                    <div class="sm:col-span-6 field-wrapper">
                        <x-ui.label for="notes">Notes</x-ui.label>
                        <div class="mt-2">
                            <x-ui.textarea name="notes" id="notes" rows="3" placeholder="Additional information, recovery codes, etc."></x-ui.textarea>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="flex items-center justify-end gap-x-3 px-8 py-6 border-t border-slate-200 bg-slate-50">
            <x-ui.button type="button" variant="ghost" @click="$dispatch('close-slide-panel','internal-account-create')">Cancel</x-ui.button>
            <x-ui.button type="submit" variant="default" form="internal-account-create-form">Create Account</x-ui.button>
        </div>
    </x-slide-panel>

    @foreach($accounts as $account)
    @if($account->isOwner())
    <x-slide-panel name="internal-account-edit-{{$account->id}}" :show="false" maxWidth="2xl">
        <div class="flex items-center justify-between px-8 py-6 border-b border-slate-200">
            <h2 class="text-2xl font-bold text-slate-900">Edit Internal Account</h2>
            <button type="button" @click="$dispatch('close-slide-panel','internal-account-edit-{{$account->id}}')" class="text-slate-400 hover:text-slate-600 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto px-8 py-6">
            <form id="internal-account-edit-form-{{$account->id}}" x-data="{loading:false,showPass:false,async submit(e){e.preventDefault();this.loading=true;document.querySelectorAll('#internal-account-edit-form-{{$account->id}} .error-message').forEach(el=>el.remove());const fd=new FormData(e.target);try{const r=await fetch('{{route('internal-accounts.update',$account)}}',{method:'POST',headers:{'X-CSRF-TOKEN':'{{csrf_token()}}','Accept':'application/json'},body:fd});const d=await r.json();if(r.ok){$dispatch('close-slide-panel','internal-account-edit-{{$account->id}}');$dispatch('toast',{message:'Internal account updated!',type:'success'});setTimeout(()=>window.location.reload(),500);}else if(d.errors){Object.keys(d.errors).forEach(k=>{const i=document.querySelector(`#internal-account-edit-form-{{$account->id}} [name='${k}']`);if(i){const w=i.closest('div');const err=document.createElement('p');err.className='error-message mt-2 text-sm text-red-600';err.textContent=d.errors[k][0];w.appendChild(err);}});$dispatch('toast',{message:'Please correct errors.',type:'error'});}}catch(err){console.error(err);$dispatch('toast',{message:'Error occurred.',type:'error'});}finally{this.loading=false;}}}\" @submit="submit">
                @csrf @method('PUT')
                <div class="grid grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-6">
                    <div class="sm:col-span-6 field-wrapper">
                        <x-ui.label for="nume_cont_aplicatie_{{$account->id}}">Account / Application Name <span class="text-red-500">*</span></x-ui.label>
                        <div class="mt-2">
                            <x-ui.input type="text" name="nume_cont_aplicatie" id="nume_cont_aplicatie_{{$account->id}}" value="{{$account->nume_cont_aplicatie}}" required placeholder="e.g., Company Bank Account, AWS Root"/>
                        </div>
                    </div>

                    <div class="sm:col-span-6 field-wrapper">
                        <x-ui.label for="platforma_{{$account->id}}">Platform <span class="text-red-500">*</span></x-ui.label>
                        <div class="mt-2">
                            <x-ui.input
                                type="text"
                                name="platforma"
                                id="platforma_{{$account->id}}"
                                required
                                value="{{$account->platforma}}"
                                placeholder="e.g., Bank Account, CRM System, Email Service"
                            />
                        </div>
                    </div>

                    <div class="sm:col-span-3 field-wrapper">
                        <x-ui.label for="url_{{$account->id}}">URL</x-ui.label>
                        <div class="mt-2">
                            <x-ui.input type="url" name="url" id="url_{{$account->id}}" value="{{$account->url}}" placeholder="https://example.com/login"/>
                        </div>
                    </div>

                    <div class="sm:col-span-3 field-wrapper">
                        <x-ui.label for="username_{{$account->id}}">Username / Email</x-ui.label>
                        <div class="mt-2">
                            <x-ui.input type="text" name="username" id="username_{{$account->id}}" value="{{$account->username}}" placeholder="admin or email@company.com"/>
                        </div>
                    </div>

                    <div class="sm:col-span-6 field-wrapper">
                        <x-ui.label for="password_{{$account->id}}">Password</x-ui.label>
                        <div class="mt-2 relative">
                            <input x-bind:type="showPass?'text':'password'" name="password" id="password_{{$account->id}}" placeholder="Leave blank to keep current password" class="pr-10 block w-full rounded-lg border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900 sm:text-sm"/>
                            <button type="button" @click="showPass=!showPass" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-slate-500 hover:text-slate-700">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                        </div>
                        <p class="mt-1 text-xs text-slate-500">Leave blank to keep the current password</p>
                    </div>

                    <div class="sm:col-span-6 field-wrapper">
                        <div class="flex items-start">
                            <div class="flex h-6 items-center">
                                <input id="accesibil_echipei_edit_{{$account->id}}" name="accesibil_echipei" type="checkbox" value="1" {{$account->accesibil_echipei?'checked':''}} class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900">
                            </div>
                            <div class="ml-3 text-sm leading-6">
                                <label for="accesibil_echipei_edit_{{$account->id}}" class="font-medium text-slate-900">Make accessible to team</label>
                                <p class="text-slate-500">Allow all team members to view this account (you remain the owner)</p>
                            </div>
                        </div>
                    </div>

                    <div class="sm:col-span-6 field-wrapper">
                        <x-ui.label for="notes_{{$account->id}}">Notes</x-ui.label>
                        <div class="mt-2">
                            <x-ui.textarea name="notes" id="notes_{{$account->id}}" rows="3" placeholder="Additional information, recovery codes, etc.">{{$account->notes}}</x-ui.textarea>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="flex items-center justify-end gap-x-3 px-8 py-6 border-t border-slate-200 bg-slate-50">
            <x-ui.button type="button" variant="ghost" @click="$dispatch('close-slide-panel','internal-account-edit-{{$account->id}}')">Cancel</x-ui.button>
            <x-ui.button type="submit" variant="default" form="internal-account-edit-form-{{$account->id}}">Update Account</x-ui.button>
        </div>
    </x-slide-panel>
    @endif
    @endforeach
</x-app-layout>
