{{--
    Financial Files Layout Component

    Provides the shared structure for all financial file views:
    - Mobile tree toggle
    - Desktop sidebar tree view
    - Main content area
    - Upload modal

    Required variables from parent:
    - $year: Current year
    - $month: Current month (nullable)
    - $category: Current category (nullable)
    - $availableYears: Collection of years
    - $allYearsSummary: Array with file counts
--}}

@props(['year', 'month' => null, 'category' => null, 'availableYears', 'allYearsSummary'])

<x-app-layout>
    <x-slot name="pageTitle">Fisiere</x-slot>

    {{-- JavaScript for file operations --}}
    <script>
        // File upload manager - handles drag & drop and file selection
        window.fileUploadManager = function() {
            return {
                newFiles: [],

                handleFileSelect(event) {
                    const files = Array.from(event.target.files);
                    this.addFiles(files);
                },

                handleDrop(event) {
                    const files = Array.from(event.dataTransfer.files);
                    this.addFiles(files);
                },

                addFiles(files) {
                    const maxSize = 10 * 1024 * 1024;
                    const allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png',
                                        'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                        'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                        'application/zip', 'application/x-rar-compressed', 'application/x-zip-compressed'];

                    files.forEach(file => {
                        if (file.size > maxSize) {
                            alert(`${file.name} este prea mare. Dimensiunea maxima este 10MB.`);
                            return;
                        }
                        if (!allowedTypes.includes(file.type) && !file.name.match(/\.(pdf|jpe?g|png|docx?|xlsx?|zip|rar)$/i)) {
                            alert(`${file.name} are un tip de fisier neacceptat.`);
                            return;
                        }
                        if (!this.newFiles.find(f => f.name === file.name && f.size === file.size)) {
                            this.newFiles.push(file);
                        }
                    });
                    this.syncFileInput();
                },

                removeNewFile(index) {
                    this.newFiles.splice(index, 1);
                    this.syncFileInput();
                },

                syncFileInput() {
                    const input = document.getElementById('file-upload-financial');
                    if (input) {
                        const dataTransfer = new DataTransfer();
                        this.newFiles.forEach(file => dataTransfer.items.add(file));
                        input.files = dataTransfer.files;
                    }
                },

                formatFileSize(bytes) {
                    if (bytes === 0) return '0 Bytes';
                    const k = 1024;
                    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                    const i = Math.floor(Math.log(bytes) / Math.log(k));
                    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
                }
            };
        };

        // Bulk file actions - handles multi-select and bulk delete
        window.bulkFileActions = function(allIds) {
            return {
                allIds: allIds,
                selectedIds: [],

                get allSelected() {
                    return this.allIds.length > 0 && this.selectedIds.length === this.allIds.length;
                },

                get someSelected() {
                    return this.selectedIds.length > 0;
                },

                toggleFile(id) {
                    if (this.selectedIds.includes(id)) {
                        this.selectedIds = this.selectedIds.filter(i => i !== id);
                    } else {
                        this.selectedIds = [...this.selectedIds, id];
                    }
                },

                toggleAll(checked) {
                    if (checked) {
                        this.selectedIds = [...this.allIds];
                    } else {
                        this.selectedIds = [];
                    }
                },

                clearSelection() {
                    this.selectedIds = [];
                },

                async bulkDelete() {
                    if (this.selectedIds.length === 0) return;

                    const count = this.selectedIds.length;
                    if (!confirm(`Esti sigur ca vrei sa stergi ${count} ${count === 1 ? 'fisier' : 'fisiere'}? Aceasta actiune nu poate fi anulata.`)) {
                        return;
                    }

                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
                        const response = await fetch('/financial/files/bulk-delete', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({ ids: this.selectedIds })
                        });

                        const result = await response.json();

                        if (response.ok) {
                            window.location.reload();
                        } else {
                            alert(result.message || 'A aparut o eroare la stergere.');
                        }
                    } catch (error) {
                        console.error('Bulk delete error:', error);
                        alert('A aparut o eroare la stergere. Va rugam incercati din nou.');
                    }
                }
            };
        };

        // Copy to clipboard helper
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                const notification = document.createElement('div');
                notification.className = 'fixed bottom-4 right-4 bg-slate-800 text-white px-4 py-2 rounded-lg shadow-lg z-50';
                notification.textContent = 'Nume copiat in clipboard!';
                document.body.appendChild(notification);
                setTimeout(() => notification.remove(), 2000);
            }).catch(err => {
                console.error('Failed to copy:', err);
            });
        }
    </script>

    <div class="h-full flex flex-col bg-slate-50">
        {{-- Mobile Navigation Toggle --}}
        <div class="lg:hidden bg-white border-b border-slate-200" x-data="{ mobileMenuOpen: false }">
            <button @click="mobileMenuOpen = !mobileMenuOpen" class="w-full px-4 py-3 flex items-center justify-between text-left">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                    </svg>
                    <span class="font-medium text-slate-900">
                        {{ $year }}
                        @if($month)
                            / {{ ucfirst(\Carbon\Carbon::create()->setMonth($month)->locale('ro')->isoFormat('MMMM')) }}
                        @endif
                        @if($category)
                            / {{ $category === 'incasare' ? 'Incasari' : ($category === 'plata' ? 'Plati' : ucfirst($category)) }}
                        @endif
                    </span>
                </div>
                <svg :class="{'rotate-180': mobileMenuOpen}" class="w-5 h-5 text-slate-600 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            {{-- Mobile Tree View --}}
            <div x-show="mobileMenuOpen" x-collapse class="border-t border-slate-200 max-h-96 overflow-y-auto bg-white">
                @include('financial.files.partials.tree-view', [
                    'year' => $year,
                    'month' => $month,
                    'category' => $category,
                    'availableYears' => $availableYears,
                    'allYearsSummary' => $allYearsSummary
                ])
            </div>
        </div>

        {{-- Main Content Area --}}
        <div class="flex-1 flex overflow-hidden">
            {{-- Left Sidebar - Desktop Tree View --}}
            <div class="w-64 bg-white border-r border-slate-200 overflow-y-auto lg:block hidden flex-shrink-0">
                @include('financial.files.partials.tree-view', [
                    'year' => $year,
                    'month' => $month,
                    'category' => $category,
                    'availableYears' => $availableYears,
                    'allYearsSummary' => $allYearsSummary
                ])
            </div>

            {{-- Right Content --}}
            <div class="flex-1 overflow-y-auto" id="file-content-area">
                {{ $slot }}
            </div>
        </div>
    </div>

    {{-- Upload Modal --}}
    @include('financial.files.partials.upload-modal', [
        'year' => $year,
        'month' => $month,
        'category' => $category
    ])

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="fixed bottom-4 right-4 bg-green-600 text-white px-6 py-3 rounded-lg shadow-lg z-50"
             x-data="{ show: true }"
             x-show="show"
             x-init="setTimeout(() => show = false, 3000)"
             x-transition>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="fixed bottom-4 right-4 bg-red-600 text-white px-6 py-3 rounded-lg shadow-lg z-50"
             x-data="{ show: true }"
             x-show="show"
             x-init="setTimeout(() => show = false, 5000)"
             x-transition>
            {{ session('error') }}
        </div>
    @endif
</x-app-layout>
