<x-app-layout>
    <x-slot name="pageTitle">{{ __("Edit") }} {{ $annex->annex_code }}</x-slot>

    <div class="p-4 md:p-6">
        {{-- Top Navigation Bar --}}
        <div class="flex items-center justify-between mb-4 md:mb-6">
            {{-- Back Button & Title --}}
            <div class="flex items-center gap-3 md:gap-4">
                <a href="{{ route('contracts.annex.show', [$contract, $annex]) }}"
                   class="inline-flex items-center justify-center w-10 h-10 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 hover:text-slate-900 transition-colors flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div class="min-w-0">
                    <h1 class="text-lg md:text-2xl font-bold text-slate-900 truncate">{{ __("Edit Annex") }}: {{ $annex->annex_code }}</h1>
                    <p class="text-sm text-slate-500 truncate">{{ __("Contract") }}: {{ $contract->formatted_number }}</p>
                </div>
            </div>
        </div>

        <form action="{{ route('contracts.annex.update', [$contract, $annex]) }}" method="POST">
            @csrf
            @method("PUT")

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Main Content --}}
                <div class="lg:col-span-2 space-y-6">
                    {{-- Editor --}}
                    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                        <div class="px-5 py-4 border-b border-slate-200 bg-slate-50">
                            <h3 class="font-semibold text-slate-900">{{ __("Annex Content") }}</h3>
                        </div>
                        <div class="p-5">
                            <textarea id="content" name="content" class="tinymce-editor">{{ old("content", $annex->content) }}</textarea>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                        <a href="{{ route('contracts.annex.show', [$contract, $annex]) }}"
                           class="w-full sm:w-auto text-center px-4 py-2 text-sm font-medium text-red-600 bg-white border border-red-300 rounded-lg hover:bg-red-50 transition-colors">
                            {{ __("Cancel") }}
                        </a>
                        <button type="submit"
                                class="w-full sm:w-auto px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                            {{ __("Save Changes") }}
                        </button>
                    </div>
                </div>

                {{-- Sidebar --}}
                <div class="space-y-6">
                    {{-- Annex Info --}}
                    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                        <div class="px-5 py-4 border-b border-slate-200 bg-slate-50">
                            <h3 class="font-semibold text-slate-900">{{ __("Annex Details") }}</h3>
                        </div>
                        <div class="p-5 space-y-4">
                            <div>
                                <label for="title" class="block text-sm font-medium text-slate-700 mb-1">{{ __("Title") }}</label>
                                <input type="text" name="title" id="title" value="{{ old("title", $annex->title) }}"
                                       class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            <div>
                                <label for="effective_date" class="block text-sm font-medium text-slate-700 mb-1">{{ __("Effective Date") }}</label>
                                <input type="date" name="effective_date" id="effective_date" 
                                       value="{{ old("effective_date", $annex->effective_date?->format("Y-m-d")) }}"
                                       class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            <div>
                                <dt class="text-sm text-slate-500">{{ __("Additional Value") }}</dt>
                                <dd class="font-medium text-slate-900">{{ number_format($annex->additional_value, 2) }} {{ $annex->currency }}</dd>
                            </div>
                        </div>
                    </div>

                    {{-- Variables --}}
                    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                        <div class="px-5 py-4 border-b border-slate-200 bg-slate-50">
                            <h3 class="font-semibold text-slate-900">{{ __("Variables") }}</h3>
                        </div>
                        <div class="p-5">
                            <p class="text-sm text-slate-500 mb-3">{{ __("Click to insert at cursor position") }}</p>
                            <div class="space-y-2">
                                @foreach(["client_name", "contract_number", "annex_code", "annex_date", "annex_value", "org_name"] as $var)
                                    <button type="button" onclick="insertVariable('{{ $var }}')"
                                            class="block w-full text-left px-3 py-2 text-sm bg-slate-50 rounded hover:bg-slate-100 transition-colors">
                                        <code class="text-blue-600">{{"{{"}}{{$var}}{{"}}"}}</code>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @push("scripts")
    <script>
        let editor;
        tinymce.init({
            selector: ".tinymce-editor",
            height: 500,
            plugins: "lists link table code fullscreen searchreplace wordcount",
            toolbar: "undo redo | blocks | bold italic underline | alignleft aligncenter alignright | bullist numlist | link table | code fullscreen",
            content_style: "body { font-family: -apple-system, BlinkMacSystemFont, sans-serif; font-size: 14px; }",
            setup: function(ed) {
                editor = ed;
            }
        });

        function insertVariable(varName) {
            if (editor) {
                editor.insertContent("{{" + varName + "}}");
            }
        }
    </script>
    @endpush
</x-app-layout>