<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Penghuni;
use App\Models\Unit;
use App\Models\Kontrak;
use App\Models\Blacklist;
use App\Imports\PenghuniImport;
use App\Models\Keluarga;
use App\Exports\PenghuniExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\AuditBatchService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PenghuniController extends Controller
{

    private function getTarifKeringanan()
    {
        return [
            'A' => [ 
                1 => ['dapat' => 120000, 'tidak' => 245000],
                2 => ['dapat' => 120000, 'tidak' => 235000],
                3 => ['dapat' => 110000, 'tidak' => 225000],
                4 => ['dapat' => 100000, 'tidak' => 220000],
                5 => ['dapat' => 90000, 'tidak' => 215000]
            ],
            'B' => [ 
                1 => ['dapat' => 120000, 'tidak' => 245000],
                2 => ['dapat' => 120000, 'tidak' => 235000],
                3 => ['dapat' => 110000, 'tidak' => 225000],
                4 => ['dapat' => 100000, 'tidak' => 220000],
                5 => ['dapat' => 90000, 'tidak' => 215000]
            ],
            'C' => [ 
                1 => ['dapat' => 120000, 'tidak' => 245000],
                2 => ['dapat' => 120000, 'tidak' => 235000],
                3 => ['dapat' => 110000, 'tidak' => 225000],
                4 => ['dapat' => 100000, 'tidak' => 220000],
                5 => ['dapat' => 90000, 'tidak' => 215000]
            ],
            'D' => [ 
                1 => ['normal' => 630000],
                2 => ['normal' => 580000],
                3 => ['normal' => 530000]
            ]
        ];
    }

    public function getAvailableUnits(Request $request)
    {
        $blok = $request->get('blok');
        
        if (!$blok) {
            return response()->json([
                'success' => false,
                'units' => []
            ]);
        }

        $units = Unit::where('kode_unit', 'LIKE', $blok . '%')
                    ->orderBy('kode_unit')
                    ->get();

        return response()->json([
            'success' => true,
            'units' => $units->map(function($unit) {
                return [
                    'id' => $unit->id,
                    'kode_unit' => $unit->kode_unit,
                    'is_available' => $unit->status === 'tersedia'
                ];
            })
        ]);
    }

    private function calculateRetribusi($kontrak)
    {
        if (!$kontrak || !$kontrak->unit) {
            return ['sewa' => 0, 'air' => 0, 'total' => 0];
        }

        $kodeUnit = $kontrak->unit->kode_unit;
        $blok = substr($kodeUnit, 0, 1);
        $lantai = null;

        if (in_array($blok, ['A', 'B', 'C'])) {
            $unitNum = (int)substr($kodeUnit, 1);
            $lantai = (int)floor($unitNum / 100);
        } elseif ($blok === 'D') {
            $unitNum = (int)substr($kodeUnit, 1);
            if ($unitNum >= 101 && $unitNum <= 110) $lantai = 1;
            elseif ($unitNum >= 211 && $unitNum <= 226) $lantai = 2;
            elseif ($unitNum >= 327 && $unitNum <= 342) $lantai = 3;
        }

        $nominalSewa = 0;
        $tarifAir = 0;

        if ($kontrak->status === 'aktif') {
            $tarifKeringanan = $this->getTarifKeringanan();
            
            if ($lantai && isset($tarifKeringanan[$blok][$lantai][$kontrak->keringanan])) {
                $nominalSewa = $tarifKeringanan[$blok][$lantai][$kontrak->keringanan];
            }
            
            if (in_array($blok, ['A', 'B', 'C'])) {
                $tarifAir = 60000;
            } elseif ($blok === 'D') {
                $tarifAir = 70000;
            }
        } else {
            $nominalSewa = $kontrak->nominal_keringanan ?? 0;
            $tarifAir = $kontrak->tarif_air ?? 0;
        }

        return [
            'sewa' => $nominalSewa,
            'air' => $tarifAir,
            'total' => $nominalSewa + $tarifAir
        ];
    }

    public function checkNik(Request $request)
    {
        try {
            $nik = $request->query('nik');
            
            if (!$nik || strlen($nik) !== 16) {
                return response()->json([
                    'exists' => false,
                    'is_blacklisted' => false,
                    'has_active_contract' => false,
                    'message' => 'NIK tidak valid'
                ], 200);
            }
            
            $blacklistEntry = Blacklist::where('nik', $nik)
                ->where('status', 'blacklist')
                ->first();
            
            if ($blacklistEntry) {
                return response()->json([
                    'exists' => false,
                    'is_blacklisted' => true,
                    'has_active_contract' => false,
                    'blacklist_info' => [
                        'nama' => strtoupper($blacklistEntry->nama),
                        'alasan' => $blacklistEntry->alasan_blacklist,
                        'tanggal' => $blacklistEntry->tanggal_blacklist ? $blacklistEntry->tanggal_blacklist->format('d M Y') : '-'
                    ],
                    'message' => 'NIK sudah ada di blacklist'
                ], 200);
            }
            
            $penghuni = Penghuni::where('nik', $nik)
                ->with(['keluarga', 'kontrakAktif'])
                ->first();
            
            if (!$penghuni) {
                return response()->json([
                    'exists' => false,
                    'is_blacklisted' => false,
                    'has_active_contract' => false,
                    'message' => 'NIK tidak ditemukan'
                ], 200);
            }
            
            $activeContract = $penghuni->kontrakAktif;
            
            if ($activeContract) {
                $unitKode = $activeContract->unit?->kode_unit ?? '-'; 

                return response()->json([
                    'exists' => true,
                    'is_blacklisted' => false,
                    'has_active_contract' => true,
                    'penghuni_info' => [
                        'id' => $penghuni->id,
                        'nama' => strtoupper($penghuni->nama),
                        'nik' => $penghuni->nik
                    ],
                    'contract_info' => [
                        'id' => $activeContract->id,
                        'unit_kode' => $unitKode, 
                        'tanggal_masuk' => $activeContract->tanggal_masuk ? $activeContract->tanggal_masuk->format('d M Y') : '-',
                        'tanggal_keluar' => $activeContract->tanggal_keluar ? $activeContract->tanggal_keluar->format('d M Y') : '-'
                    ],
                    'message' => 'NIK sudah memiliki kontrak aktif'
                ], 200);
            }
            
            $keluargaData = $penghuni->keluarga->map(function($k) {
                return [
                    'id' => $k->id,
                    'nama' => strtoupper($k->nama),
                    'nik' => $k->nik,
                    'umur' => $k->umur,
                    'jenis_kelamin' => $k->jenis_kelamin,
                    'hubungan' => $k->hubungan
                ];
            });
            
            $tanggalLahirFormatted = null;
            if ($penghuni->tanggal_lahir) {
                $tanggalLahirFormatted = is_string($penghuni->tanggal_lahir) 
                    ? $penghuni->tanggal_lahir 
                    : $penghuni->tanggal_lahir->format('Y-m-d');
            }

            return response()->json([
                'exists' => true,
                'is_blacklisted' => false,
                'has_active_contract' => false,
                'penghuni_info' => [
                    'id' => $penghuni->id,
                    'nik' => $penghuni->nik,
                    'nama' => strtoupper($penghuni->nama),
                    'tempat_lahir' => $penghuni->tempat_lahir,
                    'tanggal_lahir' => $tanggalLahirFormatted,
                    'jenis_kelamin' => $penghuni->jenis_kelamin,
                    'pekerjaan' => $penghuni->pekerjaan,
                    'no_hp' => $penghuni->no_hp,
                    'alamat_ktp' => $penghuni->alamat_ktp,
                    'keluarga' => $keluargaData
                ],
                'message' => 'Data penghuni ditemukan dan siap untuk kontrak baru'
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'exists' => false,
                'is_blacklisted' => false,
                'has_active_contract' => false,
                'error' => 'Terjadi kesalahan sistem'
            ], 500);
        }
    }

    public function index(Request $request)
    {
        $blacklistedNiks = Blacklist::where('status', 'blacklist')->pluck('nik');
        $perPage = $request->get('per_page', 10);
        $search = $request->input('search', '');

        $searchedPenghuni = collect([]);
        $searchedPenghuniIds = [];

        if ($search != '') {
            $searchedQuery = Penghuni::query()
                ->whereNotIn('nik', $blacklistedNiks)
                ->with(['kontrak' => function($q) {
                    $q->with('unit')->latest('tanggal_masuk')->limit(1);
                }])
                ->where(function($q) use ($search) {
                    $q->where('nama', 'LIKE', "%{$search}%")
                    ->orWhere('nik', 'LIKE', "%{$search}%");
                });
            
            $searchedPenghuni = $searchedQuery->get();
            
            $searchedPenghuni = $searchedPenghuni->sort(function($a, $b) {
                $kontrakA = $a->kontrak;
                $kontrakB = $b->kontrak;
                
                $isAktifA = $kontrakA && $kontrakA->status == 'aktif' ? 0 : 1;
                $isAktifB = $kontrakB && $kontrakB->status == 'aktif' ? 0 : 1;
                
                if ($isAktifA !== $isAktifB) {
                    return $isAktifA - $isAktifB;
                }
                
                if ($isAktifA == 0) {
                    if ($kontrakA && $kontrakA->tanggal_keluar && $kontrakB && $kontrakB->tanggal_keluar) {
                        $today = \Carbon\Carbon::today();
                        $sisaA = $today->diffInDays(\Carbon\Carbon::parse($kontrakA->tanggal_keluar), false);
                        $sisaB = $today->diffInDays(\Carbon\Carbon::parse($kontrakB->tanggal_keluar), false);
                        return $sisaA <=> $sisaB;
                    }
                } else {
                    if ($kontrakA && $kontrakA->tanggal_keluar && $kontrakB && $kontrakB->tanggal_keluar) {
                        return $kontrakA->tanggal_keluar <=> $kontrakB->tanggal_keluar;
                    }
                }
                
                return 0;
            });
            
            $searchedPenghuniIds = $searchedPenghuni->pluck('id')->toArray();
        }

        $query = Penghuni::query();
        $query->whereNotIn('nik', $blacklistedNiks);
        $query->whereNotIn('id', $searchedPenghuniIds);

        if ($request->has('kontrak_berakhir') && $request->kontrak_berakhir != '') {
            $days = (int)$request->kontrak_berakhir;
            $today = Carbon::today();
            $endDate = $today->copy()->addDays($days);
            
            $query->whereHas('kontrak', function($q) use ($today, $endDate) {
                $q->where('status', 'aktif')
                ->whereBetween('tanggal_keluar', [$today, $endDate]);
            });
        }

        if ($request->has('bulan_berakhir') && $request->bulan_berakhir != '') {
            $bulan = (int)$request->bulan_berakhir;
            $tahun = now()->year;
            
            $query->whereHas('kontrak', function($q) use ($bulan, $tahun) {
                $q->where('status', 'aktif')
                ->whereYear('tanggal_keluar', $tahun)
                ->whereMonth('tanggal_keluar', $bulan);
            });
        }

        if ($request->has('tahun_berakhir') && $request->tahun_berakhir != '') {
            $tahun = (int)$request->tahun_berakhir;
            
            $query->whereHas('kontrak', function($q) use ($tahun) {
                $q->where('status', 'aktif')
                ->whereYear('tanggal_keluar', $tahun);
            });
        }

        if ($request->has('status') && $request->status != '') {
            if ($request->status == 'aktif') {
                $query->whereHas('kontrak', function($q) {
                    $q->where('status', 'aktif');
                });
            } elseif ($request->status == 'tidak_aktif') {
                $query->whereDoesntHave('kontrak', function($q) {
                    $q->where('status', 'aktif');
                });
            }
        }

        if ($request->has('blok') && $request->blok != '') {
            $blok = strtoupper($request->blok);
            $query->whereHas('kontrak', function($q) use ($blok) {
                $q->where('status', 'aktif')
                ->whereHas('unit', function($q2) use ($blok) {
                    $q2->where('kode_unit', 'LIKE', $blok . '%');
                });
            });
        }

        $query->with(['kontrak' => function($q) {
            $q->with('unit')->latest('tanggal_masuk')->limit(1);
        }]);

        $allPenghuni = $query->get();

        if ($searchedPenghuni->isEmpty() && $allPenghuni->isEmpty()) {
            $semuaPenghuni = new \Illuminate\Pagination\LengthAwarePaginator(
                collect([]),
                0,
                $perPage,
                1,
                ['path' => $request->url(), 'query' => $request->query()]
            );
            
            return view('penghuni.index', [
                'semuaPenghuni' => $semuaPenghuni,
                'statusFilter' => $request->status ?? '',
                'blokFilter' => $request->blok ?? '',
                'kontrakBerakhirFilter' => $request->kontrak_berakhir ?? '',
                'bulanBerakhirFilter' => $request->bulan_berakhir ?? '',
                'tahunBerakhirFilter' => $request->tahun_berakhir ?? '',
                'highlightType' => $request->get('highlight', 'none'),
                'hasAnyKeluarStatus' => false,
                'searchedIds' => $searchedPenghuniIds
            ]);
        }
        
        $searchedPenghuni = $searchedPenghuni->map(function($penghuni) {
            if ($penghuni->kontrak) {
                $retribusi = $this->calculateRetribusi($penghuni->kontrak);
                $penghuni->kontrak->calculated_sewa = $retribusi['sewa'];
                $penghuni->kontrak->calculated_air = $retribusi['air'];
                $penghuni->kontrak->calculated_total = $retribusi['total'];
            }
            return $penghuni;
        });

        $allPenghuni = $allPenghuni->map(function($penghuni) {
            if ($penghuni->kontrak) {
                $retribusi = $this->calculateRetribusi($penghuni->kontrak);
                $penghuni->kontrak->calculated_sewa = $retribusi['sewa'];
                $penghuni->kontrak->calculated_air = $retribusi['air'];
                $penghuni->kontrak->calculated_total = $retribusi['total'];
            }
            return $penghuni;
        });

        if ($request->has('sort_by')) {
            if ($request->sort_by === 'tanggal_masuk_desc') {
                $sortedPenghuni = $allPenghuni->sortByDesc(function($penghuni) {
                    return $penghuni->kontrak && $penghuni->kontrak->tanggal_masuk 
                        ? $penghuni->kontrak->tanggal_masuk->timestamp 
                        : 0;
                });
                $allPenghuni = $sortedPenghuni;
            } elseif ($request->sort_by === 'blok') {
                $sortedPenghuni = $allPenghuni->sort(function($a, $b) {
                    $kodeA = $a->kontrak && $a->kontrak->unit ? $a->kontrak->unit->kode_unit : 'ZZZ';
                    $kodeB = $b->kontrak && $b->kontrak->unit ? $b->kontrak->unit->kode_unit : 'ZZZ';
                    
                    $blokA = substr($kodeA, 0, 1);
                    $blokB = substr($kodeB, 0, 1);
                    
                    $blokOrder = ['A' => 1, 'B' => 2, 'C' => 3, 'D' => 4];
                    $orderA = $blokOrder[$blokA] ?? 999;
                    $orderB = $blokOrder[$blokB] ?? 999;
                    
                    if ($orderA !== $orderB) {
                        return $orderA <=> $orderB;
                    }
                    
                    return strcmp($kodeA, $kodeB);
                });
                $allPenghuni = $sortedPenghuni;
            }
        }
        
        $sortedPenghuni = $allPenghuni->sort(function($a, $b) use ($request) {
            if ($request->has('sort_by') && $request->sort_by === 'blok') {
                return 0;
            }
            
            $kontrakA = $a->kontrak;
            $kontrakB = $b->kontrak;
            
            if ($request->has('tahun_berakhir') && $request->tahun_berakhir != '') {
                if ($kontrakA && $kontrakA->tanggal_keluar && $kontrakB && $kontrakB->tanggal_keluar) {
                    return $kontrakA->tanggal_keluar <=> $kontrakB->tanggal_keluar;
                }
            }
            
            if (($request->has('kontrak_berakhir') && $request->kontrak_berakhir != '') ||
                ($request->has('bulan_berakhir') && $request->bulan_berakhir != '')) {
                if ($kontrakA && $kontrakA->tanggal_keluar && $kontrakB && $kontrakB->tanggal_keluar) {
                    $today = \Carbon\Carbon::today();
                    $sisaA = $today->diffInDays(\Carbon\Carbon::parse($kontrakA->tanggal_keluar), false);
                    $sisaB = $today->diffInDays(\Carbon\Carbon::parse($kontrakB->tanggal_keluar), false);
                    return $sisaA <=> $sisaB;
                }
            }
            
            $statusA = $kontrakA && $kontrakA->status == 'aktif' ? 0 : 1;
            $statusB = $kontrakB && $kontrakB->status == 'aktif' ? 0 : 1;
            
            if ($statusA !== $statusB) {
                return $statusA - $statusB;
            }
            
            if ($statusA == 0 && $statusB == 0) {
                if ($kontrakA && $kontrakA->tanggal_masuk && $kontrakB && $kontrakB->tanggal_masuk) {
                    $today = Carbon::now();
                    $tanggalMasukA = Carbon::parse($kontrakA->tanggal_masuk);
                    $tanggalMasukB = Carbon::parse($kontrakA->tanggal_masuk);
                    
                    $nextA = Carbon::create($today->year, $tanggalMasukA->month, $tanggalMasukA->day);
                    $nextB = Carbon::create($today->year, $tanggalMasukB->month, $tanggalMasukB->day);
                    
                    if ($nextA->isPast()) $nextA->addYear();
                    if ($nextB->isPast()) $nextB->addYear();
                    
                    $diffA = $today->diffInDays($nextA, false);
                    $diffB = $today->diffInDays($nextB, false);
                    
                    return $diffA <=> $diffB;
                }
            }
            
            if ($statusA == 1 && $statusB == 1) {
                if ($kontrakA && $kontrakA->tanggal_keluar && $kontrakB && $kontrakB->tanggal_keluar) {
                    return $kontrakB->tanggal_keluar <=> $kontrakA->tanggal_keluar;
                }
            }
            
            if ($kontrakA && !$kontrakB) return -1;
            if (!$kontrakA && $kontrakB) return 1;
            
            return 0;
        });

        $finalResults = $searchedPenghuni->merge($sortedPenghuni);

        $currentPage = $request->get('page', 1);
        $offset = ($currentPage - 1) * $perPage;
        $highlightType = $request->get('highlight', 'none');
        
        $paginatedItems = $finalResults->slice($offset, $perPage)->values();
        
        $semuaPenghuni = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedItems,
            $finalResults->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );
        
        $hasAnyKeluarStatus = $finalResults->contains(function($p) {
            return $p->kontrak && $p->kontrak->status == 'keluar';
        });

        return view('penghuni.index', [
            'semuaPenghuni' => $semuaPenghuni,
            'statusFilter' => $request->status ?? '',
            'blokFilter' => $request->blok ?? '',
            'kontrakBerakhirFilter' => $request->kontrak_berakhir ?? '',
            'bulanBerakhirFilter' => $request->bulan_berakhir ?? '',
            'tahunBerakhirFilter' => $request->tahun_berakhir ?? '',
            'highlightType' => $highlightType,
            'hasAnyKeluarStatus' => $hasAnyKeluarStatus,
            'searchedIds' => $searchedPenghuniIds
        ]);
    }

    public function exportPdf(Request $request)
    {
        $query = Penghuni::query();
        
        $blacklistedNiks = Blacklist::where('status', 'blacklist')->pluck('nik');
        $query->whereNotIn('nik', $blacklistedNiks);

        $filterInfo = [];

        if ($request->has('kontrak_berakhir') && $request->kontrak_berakhir != '') {
            $days = (int)$request->kontrak_berakhir;
            $today = Carbon::today();
            $endDate = $today->copy()->addDays($days);
            
            $query->whereHas('kontrak', function($q) use ($today, $endDate) {
                $q->where('status', 'aktif')
                  ->whereBetween('tanggal_keluar', [$today, $endDate]);
            });
            
            $filterInfo[] = "Kontrak Berakhir dalam {$days} Hari";
        }

        if ($request->has('bulan_berakhir') && $request->bulan_berakhir != '') {
            $bulan = (int)$request->bulan_berakhir;
            $tahun = now()->year;
            
            $query->whereHas('kontrak', function($q) use ($bulan, $tahun) {
                $q->where('status', 'aktif')
                  ->whereYear('tanggal_keluar', $tahun)
                  ->whereMonth('tanggal_keluar', $bulan);
            });
            
            $namaBulan = Carbon::create($tahun, $bulan)->locale('id')->translatedFormat('F Y');
            $filterInfo[] = "Bulan: {$namaBulan}";
        }

        if ($request->has('tahun_berakhir') && $request->tahun_berakhir != '') {
            $tahun = (int)$request->tahun_berakhir;
            
            $query->whereHas('kontrak', function($q) use ($tahun) {
                $q->where('status', 'aktif')
                  ->whereYear('tanggal_keluar', $tahun);
            });
            
            $filterInfo[] = "Tahun: {$tahun}";
        }

        if ($request->has('status') && $request->status != '') {
            if ($request->status == 'aktif') {
                $query->whereHas('kontrak', function($q) {
                    $q->where('status', 'aktif');
                });
                $filterInfo[] = "Status: Aktif";
            } elseif ($request->status == 'tidak_aktif') {
                $query->whereDoesntHave('kontrak', function($q) {
                    $q->where('status', 'aktif');
                });
                $filterInfo[] = "Status: Tidak Aktif";
            }
        }

        if ($request->has('blok') && $request->blok != '') {
            $blok = strtoupper($request->blok);
            $query->whereHas('kontrak', function($q) use ($blok) {
                $q->where('status', 'aktif')
                ->whereHas('unit', function($q2) use ($blok) {
                    $q2->where('kode_unit', 'LIKE', $blok . '%');
                });
            });
            $filterInfo[] = "Blok: {$blok}";
        }

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama', 'LIKE', "%{$search}%")
                ->orWhere('nik', 'LIKE', "%{$search}%");
            });
            $filterInfo[] = "Pencarian: {$search}";
        }

        $query->with(['kontrak' => function($q) {
            $q->with('unit')->latest('tanggal_masuk')->limit(1);
        }]);

        $penghuni = $query->get();

        $penghuni = $penghuni->map(function($p) {
            if ($p->kontrak) {
                $retribusi = $this->calculateRetribusi($p->kontrak);
                $p->kontrak->calculated_sewa = $retribusi['sewa'];
                $p->kontrak->calculated_air = $retribusi['air'];
                $p->kontrak->calculated_total = $retribusi['total'];
            }
            return $p;
        });

        if ($request->has('tahun_berakhir') && $request->tahun_berakhir != '') {
            $penghuni = $penghuni->sort(function($a, $b) {
                $kontrakA = $a->kontrak;
                $kontrakB = $b->kontrak;
                if ($kontrakA && $kontrakA->tanggal_keluar && $kontrakB && $kontrakB->tanggal_keluar) {
                    return $kontrakA->tanggal_keluar <=> $kontrakB->tanggal_keluar;
                }
                return 0;
            });
        }

        $judulPdf = empty($filterInfo) 
            ? "Data Penghuni - Semua Data" 
            : "Data Penghuni - " . implode(', ', $filterInfo);

        $data = [
            'penghuni' => $penghuni,
            'judul' => $judulPdf,
            'filterInfo' => $filterInfo,
            'tanggalCetak' => now()->locale('id')->translatedFormat('d F Y H:i'),
            'isFilterTahun' => $request->has('tahun_berakhir') && $request->tahun_berakhir != ''
        ];

        $pdf = Pdf::loadView('penghuni.penghuni_data_pdf', $data);
        $pdf->setPaper('f4', 'portrait');

        $filename = 'Data Penghuni ' . now()->format('Ymd_His') . '.pdf';
        return $pdf->stream($filename);
    }

    public function exportExcel(Request $request)
    {
        $fileName = 'Data Penghuni ' . now()->format('Ymd_His') . '.xlsx';
        
        return Excel::download(new PenghuniExport($request), $fileName);
    }


    public function create()
    {
        return view('penghuni.create');
    }

    public function store(Request $request)
    {
        $request->merge([
            'tarif_air' => $request->tarif_air ? preg_replace('/[^0-9]/', '', $request->tarif_air) : null,
            'nominal_keringanan' => $request->nominal_keringanan ? preg_replace('/[^0-9]/', '', $request->nominal_keringanan) : null,
            'nilai_jaminan' => $request->nilai_jaminan ? preg_replace('/[^0-9]/', '', $request->nilai_jaminan) : null,
        ]);

        $validatedData = $request->validate([
            'penghuni_id' => 'required|exists:penghuni,id',
            'unit_kode' => 'required|string|exists:units,kode_unit',
            'tanggal_masuk' => 'required|date',
            'tanggal_keluar' => 'required|date|after_or_equal:tanggal_masuk',
            'keringanan' => 'required|in:dapat,tidak,normal',
            'nominal_keringanan' => 'nullable|numeric',
            'tarif_air' => 'nullable|numeric',
            'tanggal_sps' => 'nullable|date',
            'no_sps' => 'nullable|string|max:255',
            'nilai_jaminan' => 'nullable|numeric',
            'no_sip' => 'nullable|string|max:255',
            'tanggal_sip' => 'nullable|date',
            'alasan_keluar' => 'nullable|string',
        ]);

        $unit = Unit::where('kode_unit', $validatedData['unit_kode'])->firstOrFail();
        
        $dataToStore = [
            'penghuni_id' => $validatedData['penghuni_id'],
            'unit_id' => $unit->id,
            'tanggal_masuk' => $validatedData['tanggal_masuk'],
            'tanggal_keluar' => $validatedData['tanggal_keluar'],
            'keringanan' => $validatedData['keringanan'],
            'nominal_keringanan' => $validatedData['nominal_keringanan'],
            'tarif_air' => $validatedData['tarif_air'],
            'no_sps' => $validatedData['no_sps'] ?? null,
            'tanggal_sps' => $validatedData['tanggal_sps'] ?? null,
            'no_sip' => $validatedData['no_sip'] ?? null,
            'tanggal_sip' => $validatedData['tanggal_sip'] ?? null,
            'nilai_jaminan' => $validatedData['nilai_jaminan'],
            'status' => 'aktif'
        ];
        
        Kontrak::create($dataToStore);
        $penghuni = Penghuni::find($validatedData['penghuni_id']);
        $penghuni->status = 'aktif';
        $penghuni->save();
        
        $unit->status = 'terisi';
        $unit->save();

        return redirect()->route('penghuni.show', $penghuni->id)
            ->with('success', 'Kontrak berhasil diakhiri dan status penghuni telah diperbarui.');
    }

    public function cetakSuratPernyataan($id)
    {
        $penghuni = Penghuni::findOrFail($id);
        
        $data = [
            'penghuni' => $penghuni,
            'tanggal_cetak' => now()->translatedFormat('d F Y')
        ];

        $pdf = Pdf::loadView('surat.pernyataan', $data);

        $pdf->setPaper('f4', 'portrait');

        return $pdf->stream('surat_pernyataan_' . $penghuni->nama . '.pdf');
    }

    public function blacklist(Request $request, string $id)
    {
        $request->validate([
            'alasan_blacklist' => 'required',
        ]);

        try {
            DB::beginTransaction();

            $penghuni = Penghuni::findOrFail($id);
            AuditBatchService::start("Blacklist penghuni: {$penghuni->nama}");  

            $existingBlacklist = Blacklist::where('nik', $penghuni->nik)
                ->where('status', 'blacklist')
                ->first();

            if ($existingBlacklist) {
                AuditBatchService::end();
                return redirect()->back()->with('error', 'Penghuni ini sudah ada di daftar hitam!');
            }

            $kontrakAktif = Kontrak::where('penghuni_id', $penghuni->id)
                ->where('status', 'aktif')
                ->get();

            foreach ($kontrakAktif as $kontrak) {
                $kontrak->status = 'keluar';
                $kontrak->tanggal_keluar_aktual = now();
                $kontrak->alasan_keluar = 'Di-blacklist: ' . $request->alasan_blacklist;
                $kontrak->save();

                if ($kontrak->unit) {
                    $kontrak->unit->status = 'tersedia';
                    $kontrak->unit->save();
                }
            }

            Blacklist::updateOrCreate(
                ['nik' => $penghuni->nik],
                [
                    'nama' => $penghuni->nama,
                    'hubungan' => 'Penghuni Utama',
                    'alasan_blacklist' => $request->alasan_blacklist,
                    'tanggal_blacklist' => now(),
                    'status' => 'blacklist',
                    'alasan_aktivasi' => null,
                    'tanggal_aktivasi' => null,
                    'tags' => json_encode(['penghuni_name' => $penghuni->nama])
                ]
            );

            $penghuni->status = 'tidak_aktif';
            $penghuni->save();
            AuditBatchService::end();

            DB::commit();

            $namaUppercased = strtoupper($penghuni->nama);
            $message = "Penghuni {$namaUppercased} telah berhasil dimasukkan ke daftar hitam.";
            
            if ($kontrakAktif->count() > 0) {
                $message .= " {$kontrakAktif->count()} kontrak aktif telah diakhiri.";
            }

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            AuditBatchService::end();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function storeWithKontrak(Request $request)
    {
        $request->merge([
            'tarif_air' => $request->tarif_air ? preg_replace('/[^0-9]/', '', $request->tarif_air) : 0,
            'nominal_keringanan' => $request->nominal_keringanan ? preg_replace('/[^0-9]/', '', $request->nominal_keringanan) : 0,
            'nilai_jaminan' => $request->nilai_jaminan ? preg_replace('/[^0-9]/', '', $request->nilai_jaminan) : 0,
        ]);

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'nik' => [
                'required',
                'string',
                'size:16',
                function ($attribute, $value, $fail) {
                    $existingPenghuni = Penghuni::where('nik', $value)->first();
                    if ($existingPenghuni) {
                        $fail('NIK sudah terdaftar dalam sistem sebagai penghuni.');
                        return;
                    }

                    $blacklistEntry = Blacklist::where('nik', $value)
                        ->where('status', 'blacklist')
                        ->first();
                    
                    if ($blacklistEntry) {
                        $fail('NIK masih terdaftar dalam daftar hitam (blacklist). Silakan aktifkan kembali melalui menu Daftar Blacklist.');
                        return;
                    }
                },
            ],
            'nama' => 'required|string|max:255',
            'tempat_lahir' => 'nullable|string|max:255',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'nullable|in:laki-laki,perempuan',
            'pekerjaan' => 'nullable|string|max:255',
            'no_hp' => 'nullable|string|max:20',
            'alamat_ktp' => 'nullable|string',
            
            'unit_kode' => 'required|string',
            'tanggal_masuk' => 'required|date',
            'tanggal_keluar' => 'nullable|date',
            'keringanan' => 'required|in:dapat,tidak,normal',
            'nominal_keringanan' => 'required|numeric|min:0',
            'tarif_air' => 'required|numeric|min:0',
            'no_sps' => 'required|string|max:255',
            'tanggal_sps' => 'required|date',
            'no_sip' => 'required|string|max:255',
            'tanggal_sip' => 'required|date',
            'nilai_jaminan' => 'nullable|numeric|min:0',
            
            'ada_pasangan' => 'nullable|boolean',
            'pasangan_nama' => 'required_if:ada_pasangan,1|nullable|string|max:255',
            'pasangan_nik' => 'nullable|string|size:16',
            'pasangan_umur' => 'nullable|integer|min:0|max:150',
            'pasangan_jenis_kelamin' => 'required_if:ada_pasangan,1|nullable|in:laki-laki,perempuan',
            
            'jumlah_anak' => 'nullable|integer|min:0|max:10',
            'anak_nama' => 'nullable|array',
            'anak_nama.*' => 'required_with:anak_nama|string|max:255',
            'anak_umur' => 'nullable|array',
            'anak_umur.*' => 'nullable|integer|min:0|max:150',
            'anak_jenis_kelamin' => 'nullable|array',
            'anak_jenis_kelamin.*' => 'required_with:anak_jenis_kelamin|in:laki-laki,perempuan',
        ], [
            'nik.required' => 'NIK wajib diisi',
            'nik.size' => 'NIK harus terdiri dari 16 digit',
            'nama.required' => 'Nama lengkap wajib diisi',
            'unit_kode.required' => 'Unit harus dipilih',
            'tanggal_masuk.required' => 'Tanggal masuk wajib diisi',
            'keringanan.required' => 'Status keringanan wajib dipilih',
            'pasangan_nama.required_if' => 'Nama pasangan wajib diisi',
            'pasangan_jenis_kelamin.required_if' => 'Jenis kelamin pasangan wajib dipilih',
            'pasangan_nik.size' => 'NIK pasangan harus 16 digit',
            'anak_nama.*.required_with' => 'Nama anak wajib diisi',
            'anak_jenis_kelamin.*.required_with' => 'Jenis kelamin anak wajib dipilih',
            'no_sps.required' => 'Nomor SPS wajib diisi',
            'tanggal_sps.required' => 'Tanggal SPS wajib diisi',
            'no_sip.required' => 'Nomor SIP wajib diisi',
            'tanggal_sip.required' => 'Tanggal SIP wajib diisi',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', collect($validator->errors())->flatten()->first());
        }

        $validated = $validator->validated();
        $validated['nama'] = strtoupper($validated['nama']);

        if (isset($validated['tempat_lahir']) && $validated['tempat_lahir']) {
            $validated['tempat_lahir'] = strtoupper($validated['tempat_lahir']);
        }
        
        if (isset($validated['pasangan_nama']) && $validated['pasangan_nama']) {
            $validated['pasangan_nama'] = strtoupper($validated['pasangan_nama']);
        }

        try {
            DB::beginTransaction();

            $jumlahKeluarga = ($request->has('ada_pasangan') && $request->ada_pasangan ? 1 : 0) + 
                            ($validated['jumlah_anak'] ?? 0);
            
            $keluargaDesc = $jumlahKeluarga > 0 ? " dan {$jumlahKeluarga} anggota keluarga" : "";
            $deskripsiBatch = "Menambah Penghuni: {$validated['nama']} ke Unit {$validated['unit_kode']}{$keluargaDesc}";
            
            AuditBatchService::start($deskripsiBatch);

            $penghuni = Penghuni::create([
                'nik' => $validated['nik'],
                'nama' => $validated['nama'],
                'tempat_lahir' => $validated['tempat_lahir'] ?? null,
                'tanggal_lahir' => $validated['tanggal_lahir'] ?? null,
                'jenis_kelamin' => $validated['jenis_kelamin'],
                'pekerjaan' => $validated['pekerjaan'] ?? null,
                'no_hp' => $validated['no_hp'] ?? null,
                'alamat_ktp' => $validated['alamat_ktp'] ?? null,
                'status' => 'aktif',
            ]);

            if ($request->has('ada_pasangan') && $request->ada_pasangan) {
                $hubunganPasangan = $validated['pasangan_jenis_kelamin'] === 'laki-laki' ? 'suami' : 'istri';
                
                Keluarga::create([
                    'penghuni_id' => $penghuni->id,
                    'nama' => $validated['pasangan_nama'],
                    'nik' => $validated['pasangan_nik'] ?? null,
                    'umur' => $validated['pasangan_umur'] ?? null,
                    'jenis_kelamin' => $validated['pasangan_jenis_kelamin'],
                    'hubungan' => $hubunganPasangan,
                    'catatan' => null,
                ]);
            }

            $jumlahAnak = $validated['jumlah_anak'] ?? 0;
            if ($jumlahAnak > 0 && $request->has('anak_nama')) {
                $anakNama = $request->anak_nama;
                $anakUmur = $request->anak_umur ?? [];
                $anakJenisKelamin = $request->anak_jenis_kelamin ?? [];
                
                for ($i = 0; $i < count($anakNama); $i++) {
                    if (!empty($anakNama[$i])) {
                        Keluarga::create([
                            'penghuni_id' => $penghuni->id,
                            'nama' => strtoupper($anakNama[$i]),
                            'nik' => null,
                            'umur' => $anakUmur[$i] ?? null,
                            'jenis_kelamin' => $anakJenisKelamin[$i] ?? null,
                            'hubungan' => 'anak',
                            'catatan' => null,
                        ]);
                    }
                }
            }

            $unit = Unit::where('kode_unit', $validated['unit_kode'])->first();
            
            if (!$unit) {
                $unit = Unit::create([
                    'kode_unit' => $validated['unit_kode'],
                    'tipe' => $this->getUnitType($validated['unit_kode']),
                    'status' => 'tersedia',
                    'harga_sewa' => 0
                ]);
            }

            $existingKontrak = Kontrak::where('unit_id', $unit->id)
                ->where('status', 'aktif')
                ->first();
                
            if ($existingKontrak) {
                AuditBatchService::end();
                DB::rollBack();
                
                return back()
                    ->withInput()
                    ->with('error', 'Unit ini sudah terisi oleh penghuni lain!');
            }

            $kontrak = Kontrak::create([
                'penghuni_id' => $penghuni->id,
                'unit_id' => $unit->id,
                'tanggal_masuk' => $validated['tanggal_masuk'],
                'tanggal_keluar' => $validated['tanggal_keluar'] ?? null,
                'keringanan' => $validated['keringanan'],
                'nominal_keringanan' => $validated['nominal_keringanan'],
                'tarif_air' => $validated['tarif_air'],
                'no_sps' => $validated['no_sps'] ?? null,
                'tanggal_sps' => $validated['tanggal_sps'] ?? null,
                'no_sip' => $validated['no_sip'] ?? null,
                'tanggal_sip' => $validated['tanggal_sip'] ?? null,
                'nilai_jaminan' => $validated['nilai_jaminan'] ?? 0,
                'status' => 'aktif',
            ]);

            $unit->status = 'terisi';
            $unit->save();

            AuditBatchService::end(); 

            DB::commit();

            $successMessage = 'Data penghuni dan kontrak berhasil disimpan!';
            if ($jumlahKeluarga > 0) {
                $successMessage = "Data penghuni, kontrak, dan {$jumlahKeluarga} anggota keluarga berhasil disimpan!";
            }

            return redirect()
                ->route('penghuni.show', $penghuni->id)
                ->with('success', $successMessage);
        }
        catch (\Exception $e) {
            AuditBatchService::end();
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }
    }
    private function getUnitType($kodeUnit)
    {
        $blok = substr($kodeUnit, 0, 1);
        
        if (in_array($blok, ['A', 'B', 'C'])) {
            return 'Rumah Susun Sederhana';
        } elseif ($blok === 'D') {
            return 'MBR Tegalsari';
        } elseif ($blok === 'P') {
            return 'Prototipe Tegalsari';
        }
        
        return 'Unknown';
    }

    public function show(string $id)
    {
        if (strlen($id) == 16 && is_numeric($id)) {
            $penghuni = Penghuni::where('nik', $id)
                            ->with('semuaKontrak.unit', 'keluarga')
                            ->firstOrFail();
        } else {
            $penghuni = Penghuni::with('semuaKontrak.unit', 'keluarga')->findOrFail($id);
        }

        $penghuni->semuaKontrak->map(function($kontrak) {
            $retribusi = $this->calculateRetribusi($kontrak);
            $kontrak->calculated_sewa = $retribusi['sewa'];
            $kontrak->calculated_air = $retribusi['air'];
            $kontrak->calculated_total = $retribusi['total'];
            return $kontrak;
        });
        $unitTerisiIds = Kontrak::where('status', 'aktif')->pluck('unit_id');
        $unitKosong = Unit::whereNotIn('id', $unitTerisiIds)->orderBy('kode_unit')->get();
        
        return view('penghuni.show', [
            'penghuni' => $penghuni,
            'unitKosong' => $unitKosong
        ]);
    }

    public function edit(string $id)
    {
        $penghuni = Penghuni::findOrFail($id);
        return view('penghuni.edit', [
            'penghuni' => $penghuni
        ]);
    }
    
    public function update(Request $request, string $id)
    {
        $penghuni = Penghuni::findOrFail($id);
        $validatedData = $request->validate([
            'nik' => 'required|string|size:16|unique:penghuni,nik,' . $penghuni->id,
            'nama' => 'required|string|max:255',
            'tempat_lahir' => 'nullable|string',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'nullable|in:laki-laki,perempuan',
            'pekerjaan' => 'nullable|string',
            'no_hp' => 'nullable|string',
            'alamat_ktp' => 'nullable|string',
        ]);

        $validatedData['nama'] = strtoupper($validatedData['nama']);
        
        $penghuni->update($validatedData);

        return redirect()->route('penghuni.show', $penghuni->id)->with('success', 'Data penghuni berhasil diperbarui!');
    }

    public function destroy(string $id)
    {
        $penghuni = Penghuni::findOrFail($id);
        $penghuni->delete();
        return redirect()->route('penghuni.index')->with('success', 'Data penghuni berhasil dihapus!');
    }
    
    public function import()
    {
        return view('penghuni.import');
    }

    public function importStore(Request $request)   
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:5120',
        ]);

        $file = $request->file('file');
        
        if (!$file || !$file->isValid()) {
            return back()->with('error', 'File tidak valid atau corrupt!');
        }

        $nama_file = rand() . '_' . $file->getClientOriginalName();
        $temp_dir = sys_get_temp_dir();
        $file->move($temp_dir, $nama_file);
        $path = $temp_dir . DIRECTORY_SEPARATOR . $nama_file;
        
        try {
            config(['excel.temporary_files.local_path' => $temp_dir]);
            config(['excel.temporary_files.remote_disk' => null]);
            
            DB::beginTransaction();
            
            AuditBatchService::start("Import data Penghuni dari Excel: {$file->getClientOriginalName()}");
            
            Excel::import(new PenghuniImport, $path);
            
            AuditBatchService::end();
            
            DB::commit();

            if (file_exists($path)) {
                unlink($path);
            }
            
            return redirect()->route('penghuni.index')
                ->with('success', 'Data berhasil diimport dari Excel!');
                
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            AuditBatchService::end();
            DB::rollBack();
            
            if (file_exists($path)) {
                unlink($path);
            }
            
            $failures = $e->failures();
            $errorMessages = [];
            
            foreach ($failures as $failure) {
                $errorMessages[] = 'Baris ' . $failure->row() . ': ' . implode(', ', $failure->errors());
            }
            
            return back()->with('error', 'Validasi gagal: ' . implode(' | ', $errorMessages));
            
        } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
            AuditBatchService::end();
            DB::rollBack();
            
            if (file_exists($path)) {
                unlink($path);
            }
            
            return back()->with('error', 'Error membaca file Excel: ' . $e->getMessage());
            
        } catch (\Exception $e) {
            AuditBatchService::end();
            DB::rollBack();
            
            if (file_exists($path)) {
                unlink($path);
            }
            
            return back()->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }
}