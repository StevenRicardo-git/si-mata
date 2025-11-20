@extends('layouts.app')

@section('title', 'Jejak Audit')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/audit.css') }}">
@endpush

@section('content')
<div class="rounded-lg border bg-white p-6 shadow-sm">
    <h2 class="mb-6 text-2xl font-bold text-gray-800">Jejak Audit Sistem</h2>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="w-12 px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500"></th>
                    <th class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">Waktu</th>
                    <th class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">Pengguna</th>
                    <th class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">Aksi</th>
                    <th class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">Data Terkait</th>
                    <th class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">Ringkasan</th>
                    <th class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">Detail</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                
                @forelse ($audits as $auditItem)
                @php
                    $mainAudit = $auditItem;
                    $auditGroup = $auditItem->batch_audits ?? collect([$auditItem]);
                    $isGroup = $auditGroup->count() > 1;
                @endphp
                
                <tr class="hover:bg-gray-50">
                    <td class="whitespace-nowrap px-4 py-4 text-center text-sm text-gray-900">
                        @if($isGroup)
                            <button class="js-audit-toggle mx-auto" data-target="batch-{{ $mainAudit->id }}" title="Lihat detail grup">
                                <svg class="w-5 h-5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </button>
                        @endif
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-center text-sm text-gray-900">
                        {{ $mainAudit->created_at->format('d/m/Y H:i:s') }}
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-center text-sm text-gray-500">
                        {{ $mainAudit->user->name ?? 'SISTEM' }}
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-center text-sm font-medium">
                        @if($isGroup)
                            <span class="rounded-full bg-purple-100 px-2.5 py-0.5 text-xs font-semibold text-purple-800">
                                BATCH ({{ $auditGroup->count() }} operasi)
                            </span>
                        @else
                            @if ($mainAudit->event == 'created')
                                <span class="rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-800">MEMBUAT</span>
                            @elseif ($mainAudit->event == 'updated')
                                <span class="rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-semibold text-yellow-800">MEMPERBARUI</span>
                            @elseif ($mainAudit->event == 'deleted')
                                <span class="rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-semibold text-red-800">MENGHAPUS</span>
                            @else
                                <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-800">{{ strtoupper($mainAudit->event) }}</span>
                            @endif
                        @endif
                    </td>
                    
                    <td class="px-6 py-4 text-center text-sm text-gray-700">
                        @php
                            $modelTypes = $auditGroup->map(function($audit) {
                                return class_basename($audit->auditable_type);
                            })->unique();
                            
                            $displayName = \App\Helpers\AuditDisplayHelper::getDisplayName($mainAudit);
                        @endphp

                        @if($isGroup)
                            <div class="text-xs text-gray-500">
                                @if($modelTypes->count() > 2)
                                    {{ $modelTypes->take(2)->implode(', ') }}, +{{ $modelTypes->count() - 2 }} lainnya
                                @else
                                    {{ $modelTypes->implode(', ') }}
                                @endif
                            </div>
                        @else
                            <span>{{ $displayName }}</span>
                        @endif
                    </td>

                    <td class="px-6 py-4 text-left text-sm">
                        @php
                            $summary = $isGroup && $mainAudit->batch_description
                                ? $mainAudit->batch_description
                                : \App\Helpers\AuditDisplayHelper::getSummary($mainAudit);
                        @endphp
                        <div class="font-medium text-gray-900">{{ $summary }}</div>
                    </td>

                    <td class="whitespace-nowrap px-6 py-4 text-center text-sm">
                        @if($isGroup)
                            <button type="button" 
                                    onclick="toggleBatchDetails('batch-{{ $mainAudit->id }}')"
                                    class="font-medium text-purple-600 hover:underline">
                                Lihat Detail Batch
                            </button>
                        @else
                            <button type="button" 
                                    class="font-medium text-primary hover:underline js-audit-detail-btn"
                                    data-audit='@json($mainAudit->makeHidden(["batch_audits", "batch_count", "batch_description"]))'>
                                Lihat Detail
                            </button>
                        @endif
                    </td>
                </tr>
                
                @if($isGroup)
                    @foreach($auditGroup as $childAudit)
                        @php
                            $childDisplayName = \App\Helpers\AuditDisplayHelper::getDisplayName($childAudit);
                            $childSummary = \App\Helpers\AuditDisplayHelper::getSummary($childAudit);
                        @endphp
                        <tr class="audit-child-row bg-gray-50 hidden" data-parent="batch-{{ $mainAudit->id }}">
                            <td></td>
                            <td class="px-6 py-3 text-right text-xs text-gray-500">
                                &#8627; {{ $childAudit->created_at->format('H:i:s') }}
                            </td>
                            <td></td>
                            <td class="px-6 py-3 text-center text-sm font-medium">
                                @if ($childAudit->event == 'created')
                                    <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-800">MEMBUAT</span>
                                @elseif ($childAudit->event == 'updated')
                                    <span class="rounded-full bg-yellow-100 px-2 py-0.5 text-xs font-semibold text-yellow-800">MEMPERBARUI</span>
                                @elseif ($childAudit->event == 'deleted')
                                    <span class="rounded-full bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-800">MENGHAPUS</span>
                                @else
                                    <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-800">{{ strtoupper($childAudit->event) }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-left text-sm text-gray-600">
                                {{ $childSummary }}
                            </td>
                            <td class="px-6 py-3 text-center text-sm text-gray-600">
                                {{ $childDisplayName }}
                            </td>
                            <td class="px-6 py-3 text-center text-sm">
                                <button type="button" 
                                        class="font-medium text-primary hover:underline js-audit-detail-btn"
                                        data-audit='@json($childAudit->makeHidden(["batch_audits", "batch_count", "batch_description"]))'>
                                    Lihat Detail
                                </button>
                            </td>
                        </tr>
                    @endforeach
                @endif
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-sm text-gray-500">
                        Tidak ada data jejak audit ditemukan.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6 flex items-center justify-between">
        <p class="text-sm text-gray-600">
            Menampilkan
            <span class="font-bold">{{ $audits->firstItem() ?? 0 }}</span>
            sampai
            <span class="font-bold">{{ $audits->lastItem() ?? 0 }}</span>
            dari
            <span class="font-bold">{{ $audits->total() }}</span>
            data
        </p>    

        <nav class="flex items-center space-x-1">
            <a href="{{ $audits->previousPageUrl() }}" class="{{ $audits->onFirstPage() ? 'pointer-events-none text-gray-400' : 'hover:bg-gray-100' }} px-3 py-1 border rounded-lg">Sebelumnya</a>

            @php
                $currentPage = $audits->currentPage();
                $lastPage = $audits->lastPage();
                $window = 2;
                $showPages = [];

                if ($lastPage <= 7) {
                    for ($i = 1; $i <= $lastPage; $i++) $showPages[] = $i;
                } else {
                    $showPages[] = 1;
                    if ($currentPage > $window + 2) {
                        $showPages[] = ['type' => 'gap', 'page' => floor(($currentPage - $window + 1) / 2)];
                    }
                    for ($i = max(2, $currentPage - $window); $i <= min($lastPage - 1, $currentPage + $window); $i++) {
                        $showPages[] = $i;
                    }
                    if ($currentPage < $lastPage - $window - 1) {
                        $showPages[] = ['type' => 'gap', 'page' => floor(($currentPage + $window + $lastPage) / 2)];
                    }
                    if ($lastPage > 1) $showPages[] = $lastPage;
                }
            @endphp

            @foreach (array_unique($showPages, SORT_REGULAR) as $page)
                @if (is_array($page))
                    <a href="{{ $audits->url($page['page']) }}" class="relative px-3 py-1 text-center text-gray-400 opacity-75 hover:text-primary transition-colors" title="Lompat ke halaman {{ $page['page'] }}">
                        <span class="text-xs">{{ $page['page'] }}</span>
                        <span class="absolute -bottom-2 left-1/2 -translate-x-1/2 text-xs">...</span>
                    </a>
                @elseif ($page == $currentPage)
                    <span class="px-3 py-1 bg-primary text-white border border-primary rounded-lg z-10">{{ $page }}</span>
                @else
                    <a href="{{ $audits->url($page) }}" class="px-3 py-1 border rounded-lg hover:bg-gray-100">{{ $page }}</a>
                @endif
            @endforeach

            <a href="{{ $audits->nextPageUrl() }}" class="{{ !$audits->hasMorePages() ? 'pointer-events-none text-gray-400' : 'hover:bg-gray-100' }} px-3 py-1 border rounded-lg">Selanjutnya</a>
        </nav>
    </div>
</div>

<div id="auditDetailModal" 
     class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4" 
     style="backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);"
     onclick="closeModal('auditDetailModal')">
    
    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full mx-4 transform animate-slideUp"
         id="auditModalContent"
         onclick="event.stopPropagation()">
        
        <div class="flex items-center justify-between border-b p-6">
            <h3 class="text-2xl font-bold text-gray-900">Detail Perubahan Audit</h3>
            <button onclick="closeModal('auditDetailModal')" 
                    class="p-2 rounded-lg text-gray-400 hover:bg-gray-100 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <div class="p-6 max-h-[70vh] overflow-y-auto">
            <div id="auditDetailInfo" class="mb-6 grid grid-cols-2 gap-4 text-sm">
            </div>
            
            <div id="auditDetailContent">
            </div>
        </div>

        <div class="border-t bg-gray-50 px-6 py-4 rounded-b-2xl">
            <button onclick="closeModal('auditDetailModal')"
                    class="w-full bg-gray-200 text-gray-700 font-bold py-2.5 px-6 rounded-lg hover:bg-gray-300 transition-colors">
                Tutup
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/pages/audit.js') }}"></script>
@endpush