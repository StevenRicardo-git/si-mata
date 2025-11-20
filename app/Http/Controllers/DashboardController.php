<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Penghuni;
use App\Models\Kontrak;
use App\Models\Unit;
use App\Models\Blacklist;
use App\Models\Keluarga;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
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

    private function generateAllPossibleUnits()
    {
        $units = [];

        foreach (['A', 'B', 'C'] as $blok) {
            for ($unit = 101; $unit <= 103; $unit++) {
                $units[] = "{$blok}{$unit}";
            }

            for ($unit = 201; $unit <= 224; $unit++) {
                $units[] = "{$blok}{$unit}";
            }

            for ($unit = 301; $unit <= 324; $unit++) {
                $units[] = "{$blok}{$unit}";
            }

            for ($unit = 401; $unit <= 424; $unit++) {
                $units[] = "{$blok}{$unit}";
            }

            for ($unit = 501; $unit <= 524; $unit++) {
                $units[] = "{$blok}{$unit}";
            }
        }

        for ($unit = 101; $unit <= 110; $unit++) {
            $units[] = "D{$unit}";
        }

        for ($unit = 211; $unit <= 226; $unit++) {
            $units[] = "D{$unit}";
        }
        
        for ($unit = 327; $unit <= 342; $unit++) {
            $units[] = "D{$unit}";
        }
        
        return $units;
    }

    private function calculateRetribusi($kontrak)
    {
        if (!$kontrak || !$kontrak->unit) {
            return 0;
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

        return $nominalSewa + $tarifAir;
    }

    private function getJenisKelaminData()
    {
        $blacklistedNiks = Blacklist::where('status', 'blacklist')->pluck('nik');

        $penghuniAktif = Penghuni::whereNotIn('nik', $blacklistedNiks)
            ->whereHas('kontrak', function($q) {
                $q->where('status', 'aktif');
            })
            ->get(['id', 'jenis_kelamin']);

        $lakiLaki = $penghuniAktif->where('jenis_kelamin', 'laki-laki')->count();
        $perempuan = $penghuniAktif->where('jenis_kelamin', 'perempuan')->count();
        $penghuniIds = $penghuniAktif->pluck('id');
        $keluarga = Keluarga::whereIn('penghuni_id', $penghuniIds)
            ->get(['jenis_kelamin']);
        
        $lakiLaki += $keluarga->where('jenis_kelamin', 'laki-laki')->count();
        $perempuan += $keluarga->where('jenis_kelamin', 'perempuan')->count();

        return [
            'laki_laki' => $lakiLaki,
            'perempuan' => $perempuan,
            'total' => $lakiLaki + $perempuan
        ];
    }

    private function getKelompokUmurData()
    {
        $blacklistedNiks = Blacklist::where('status', 'blacklist')->pluck('nik');
        
        $penghuniAktif = Penghuni::whereNotIn('nik', $blacklistedNiks)
            ->whereHas('kontrak', function($q) {
                $q->where('status', 'aktif');
            })
            ->get(['id', 'tanggal_lahir']);

        $penghuniIds = $penghuniAktif->pluck('id');
        $keluarga = Keluarga::whereIn('penghuni_id', $penghuniIds)
            ->get(['umur']);

        $kelompok = [
            '0-5' => 0,
            '5-12' => 0,
            '12-18' => 0,
            '18-60' => 0,
            '60+' => 0
        ];

        foreach ($penghuniAktif as $penghuni) {
            if ($penghuni->tanggal_lahir) {
                $umur = Carbon::parse($penghuni->tanggal_lahir)->age;
                $this->kategorikanUmur($umur, $kelompok);
            }
        }

        foreach ($keluarga as $anggota) {
            if ($anggota->umur !== null && $anggota->umur >= 0) {
                $this->kategorikanUmur($anggota->umur, $kelompok);
            }
        }

        return $kelompok;
    }

    private function kategorikanUmur($umur, &$kelompok)
    {
        if ($umur >= 0 && $umur < 5) {
            $kelompok['0-5']++;
        } elseif ($umur >= 5 && $umur < 12) {
            $kelompok['5-12']++;
        } elseif ($umur >= 12 && $umur < 18) {
            $kelompok['12-18']++;
        } elseif ($umur >= 18 && $umur < 60) {
            $kelompok['18-60']++;
        } elseif ($umur >= 60) {
            $kelompok['60+']++;
        }
    }

    private function getPenghuniAktifByPeriod($bulan, $tahun)
    {
        $startOfMonth = Carbon::create($tahun, $bulan, 1)->startOfMonth();
        $endOfMonth = Carbon::create($tahun, $bulan, 1)->endOfMonth();

        $blacklistedNiks = Blacklist::where('status', 'blacklist')->pluck('nik');

        $penghuni = Penghuni::whereNotIn('nik', $blacklistedNiks)
            ->whereHas('kontrak', function($q) use ($startOfMonth, $endOfMonth) {
                $q->where('status', 'aktif')
                  ->where('tanggal_masuk', '<=', $endOfMonth)
                  ->where(function($sub) use ($startOfMonth) {
                      $sub->whereNull('tanggal_keluar')
                         ->orWhere('tanggal_keluar', '>=', $startOfMonth);
                  });
            })
            ->with(['kontrak' => function($q) use ($startOfMonth, $endOfMonth) {
                $q->with('unit')
                  ->where('status', 'aktif')
                  ->where('tanggal_masuk', '<=', $endOfMonth)
                  ->where(function($sub) use ($startOfMonth) {
                      $sub->whereNull('tanggal_keluar')
                         ->orWhere('tanggal_keluar', '>=', $startOfMonth);
                  })
                  ->latest('tanggal_masuk')
                  ->limit(1);
            }])
            ->get();

        $penghuni = $penghuni->map(function($p) {
            if ($p->kontrak) {
                $retribusi = $this->calculateRetribusi($p->kontrak);
                $p->kontrak->calculated_total = $retribusi;
            }
            return $p;
        });

        $penghuni = $penghuni->sort(function($a, $b) {
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

        return $penghuni->values();
    }

    public function index(Request $request)
    {
        $today = Carbon::now();
        $thisMonth = $today->month;
        $thisYear = $today->year;
        $lastMonth = $today->copy()->subMonth();
        $filterBulan = $request->get('filter_bulan', $thisMonth);
        $filterTahun = $request->get('filter_tahun', $thisYear);
        $isPostgres = DB::connection()->getDriverName() === 'pgsql';
        
        if ($isPostgres) {
            $sqlDiffUnder60 = "DATE_PART('day', created_at - tanggal_masuk) <= 60";
            $sqlDiffOver60 = "DATE_PART('day', created_at - tanggal_masuk) > 60";
        } else {
            $sqlDiffUnder60 = "DATEDIFF(created_at, tanggal_masuk) <= 60";
            $sqlDiffOver60 = "DATEDIFF(created_at, tanggal_masuk) > 60";
        }
        $allPossibleUnits = $this->generateAllPossibleUnits();
        $totalUnitSeharusnya = count($allPossibleUnits);
        $startOfMonth = $today->copy()->startOfMonth();
        $endOfMonth = $today->copy()->endOfMonth();
        $unitTerisi = Kontrak::where('status', 'aktif')
            ->where('tanggal_masuk', '<=', $endOfMonth)
            ->where(function($q) use ($startOfMonth) {
                $q->whereNull('tanggal_keluar')
                  ->orWhere('tanggal_keluar', '>=', $startOfMonth);
            })
            ->distinct()
            ->count('unit_id');

        $blacklistedNiks = Blacklist::where('status', 'blacklist')->pluck('nik');
        
        $penghuniAktif = DB::table('kontrak')
            ->join('penghuni', 'kontrak.penghuni_id', '=', 'penghuni.id')
            ->whereNotIn('penghuni.nik', $blacklistedNiks)
            ->where('kontrak.status', 'aktif')
            ->where('kontrak.tanggal_masuk', '<=', $endOfMonth)
            ->where(function($q) use ($startOfMonth) {
                $q->whereNull('kontrak.tanggal_keluar')
                  ->orWhere('kontrak.tanggal_keluar', '>=', $startOfMonth);
            })
            ->distinct('kontrak.penghuni_id')
            ->count('kontrak.penghuni_id');

        $nextMonth = $today->copy()->addMonth();
        $nextMonthStart = $nextMonth->copy()->startOfMonth();
        $nextMonthEnd = $nextMonth->copy()->endOfMonth();

        $kontrakAktif = Kontrak::with('unit')
            ->where('status', 'aktif')
            ->where('tanggal_masuk', '<=', $today) 
            ->where('tanggal_masuk', '<=', $endOfMonth)
            ->where(function($q) use ($startOfMonth) {
                $q->whereNull('tanggal_keluar')
                ->orWhere('tanggal_keluar', '>=', $startOfMonth);
            })
            ->get();

        $potensiRetribusiBulanIni = 0;
        $retribusiPerBlok = [
            'A' => 0,
            'B' => 0,
            'C' => 0,
            'D' => 0,
        ];

        foreach ($kontrakAktif as $kontrak) {
            $retribusi = $this->calculateRetribusi($kontrak);
            $potensiRetribusiBulanIni += $retribusi;

            if ($kontrak->unit) {
                $blok = substr($kontrak->unit->kode_unit, 0, 1);
                if (isset($retribusiPerBlok[$blok])) {
                    $retribusiPerBlok[$blok] += $retribusi;
                }
            }
        }

        $kontrakAktifBulanLalu = Kontrak::with('unit')
            ->where('status', 'aktif')
            ->where('tanggal_masuk', '<=', $lastMonth->endOfMonth())
            ->where(function($q) use ($lastMonth) {
                $q->whereNull('tanggal_keluar')
                  ->orWhere('tanggal_keluar', '>=', $lastMonth->startOfMonth());
            })
            ->get();

        $potensiRetribusiBulanLalu = 0;
        foreach ($kontrakAktifBulanLalu as $kontrak) {
            $potensiRetribusiBulanLalu += $this->calculateRetribusi($kontrak);
        }

        $perubahanRetribusi = 0;
        if ($potensiRetribusiBulanLalu > 0) {
            $perubahanRetribusi = (($potensiRetribusiBulanIni - $potensiRetribusiBulanLalu) / $potensiRetribusiBulanLalu) * 100;
        }

        $nextMonth = $today->copy()->addMonth();
        $nextMonthStart = $nextMonth->copy()->startOfMonth();
        $nextMonthEnd = $nextMonth->copy()->endOfMonth();

        $kontrakAktifBulanDepan = Kontrak::with('unit')
            ->where('status', 'aktif')
            ->where('tanggal_masuk', '<=', $nextMonthEnd) 
            ->where(function($q) use ($nextMonthStart) {
                $q->whereNull('tanggal_keluar')
                ->orWhere('tanggal_keluar', '>=', $nextMonthStart); 
            })
            ->get();

        $potensiRetribusiBulanDepan = 0;
        foreach ($kontrakAktifBulanDepan as $kontrak) {
            $potensiRetribusiBulanDepan += $this->calculateRetribusi($kontrak);
        }

        $perubahanBulanDepan = 0;
        if ($potensiRetribusiBulanIni > 0) {
            $perubahanBulanDepan = (($potensiRetribusiBulanDepan - $potensiRetribusiBulanIni) / $potensiRetribusiBulanIni) * 100;
        }

        $kontrakAkanBerakhir = Kontrak::with(['penghuni', 'unit'])
            ->where('status', 'aktif')
            ->where('tanggal_keluar', '>=', $today->copy())
            ->whereBetween('tanggal_keluar', [
                $today->copy(),
                $today->copy()->addDays(30)
            ])
            ->orderBy('tanggal_keluar', 'asc')
            ->limit(5)
            ->get();

        $kontrakExpired = Kontrak::with(['penghuni', 'unit'])
            ->where('status', 'aktif')
            ->where(function($q) use ($today) {
                $q->where('tanggal_keluar', '<', $today)
                  ->orWhere('tanggal_keluar_aktual', '<', $today);
            })
            ->orderBy('tanggal_keluar', 'asc')
            ->limit(5)
            ->get();

        $thirtyDaysAgo = $today->copy()->subDays(30)->startOfDay();
        $todayEnd = $today->copy()->endOfDay();
        
        $kontrakDiperbaruiCount = Kontrak::where('status', 'aktif')
            ->where(function($q) use ($thirtyDaysAgo, $todayEnd, $sqlDiffUnder60, $sqlDiffOver60) {
                $q->where(function($sub) use ($thirtyDaysAgo, $todayEnd, $sqlDiffUnder60) {
                    $sub->whereBetween('created_at', [$thirtyDaysAgo, $todayEnd])
                        ->whereRaw($sqlDiffUnder60);
                })
                ->orWhere(function($sub) use ($thirtyDaysAgo, $todayEnd, $sqlDiffOver60) {
                    $sub->whereBetween('tanggal_masuk', [$thirtyDaysAgo, $todayEnd])
                        ->whereRaw($sqlDiffOver60);
                });
            })
            ->count();
            
        $kontrakDiperbarui = Kontrak::with(['penghuni', 'unit'])
            ->where('status', 'aktif')
            ->where(function($q) use ($thirtyDaysAgo, $todayEnd, $sqlDiffUnder60, $sqlDiffOver60) {
                $q->where(function($sub) use ($thirtyDaysAgo, $todayEnd, $sqlDiffUnder60) {
                    $sub->whereBetween('created_at', [$thirtyDaysAgo, $todayEnd])
                        ->whereRaw($sqlDiffUnder60);
                })
                ->orWhere(function($sub) use ($thirtyDaysAgo, $todayEnd, $sqlDiffOver60) {
                    $sub->whereBetween('tanggal_masuk', [$thirtyDaysAgo, $todayEnd])
                        ->whereRaw($sqlDiffOver60);
                });
            })
            ->orderByRaw("CASE 
                WHEN $sqlDiffUnder60 THEN created_at 
                ELSE tanggal_masuk 
            END DESC")
            ->limit(5)
            ->get();
        
        $startOfThisMonth = $today->copy()->startOfMonth();
        $endOfNextMonth = $today->copy()->addMonth()->endOfMonth();

        $penghuniTerbaru = Penghuni::with(['kontrak' => function($q) {
                $q->with('unit')->orderBy('tanggal_masuk', 'asc');
            }])
            ->whereNotIn('nik', $blacklistedNiks)
            ->whereHas('kontrak', function($q) use ($startOfThisMonth, $endOfNextMonth) {
                $q->where('status', 'aktif')
                  ->whereBetween('tanggal_masuk', [$startOfThisMonth, $endOfNextMonth]);
            })
            ->get()
            ->map(function($penghuni) {
                if ($penghuni->kontrak) {
                    $retribusi = $this->calculateRetribusi($penghuni->kontrak);
                    $penghuni->kontrak->calculated_sewa = $retribusi;
                    $penghuni->kontrak->calculated_air = ($penghuni->kontrak->tarif_air ?? 0);
                    $penghuni->kontrak->calculated_total = $retribusi;
                }
                return $penghuni;
            })
            ->sortBy(function($penghuni) {
                return $penghuni->kontrak->tanggal_masuk;
            });

        $penghuniAktifPeriod = $this->getPenghuniAktifByPeriod($filterBulan, $filterTahun);
        $jumlahPenghuniAktifPeriod = $penghuniAktifPeriod->count();
        $bulanOptions = [];
        $tahunOptions = [];
        $startYear = $today->year - 1;
        $endYear = $today->year + 5;
        
        for ($y = $startYear; $y <= $endYear; $y++) {
            $tahunOptions[] = $y;
        }

        $totalUnits = [
            'A' => 99, 
            'B' => 99,
            'C' => 99,
            'D' => 42   
        ];

        $unitStatistik = [];
        foreach (['A', 'B', 'C', 'D'] as $blok) {
            $terisi = Kontrak::where('status', 'aktif')
                ->whereHas('unit', function($q) use ($blok) {
                    $q->where('kode_unit', 'LIKE', $blok . '%');
                })
                ->count();
            
            $potensiPerLantai = [];
            
            if (in_array($blok, ['A', 'B', 'C'])) {
                for ($lantai = 1; $lantai <= 5; $lantai++) {
                    $kontrakLantai = Kontrak::with('unit')
                        ->where('status', 'aktif')
                        ->whereHas('unit', function($q) use ($blok, $lantai) {
                            $q->where('kode_unit', 'LIKE', $blok . $lantai . '%');
                        })
                        ->get();
                    
                    $potensiLantai = 0;
                    foreach ($kontrakLantai as $kontrak) {
                        $potensiLantai += $this->calculateRetribusi($kontrak);
                    }
                    
                    $potensiPerLantai[$lantai] = [
                        'potensi' => $potensiLantai,
                        'terisi' => $kontrakLantai->count()
                    ];
                }
            } elseif ($blok === 'D') {
                $lantaiRanges = [
                    1 => ['start' => 101, 'end' => 110],
                    2 => ['start' => 211, 'end' => 226],
                    3 => ['start' => 327, 'end' => 342]
                ];
                
                foreach ($lantaiRanges as $lantai => $range) {
                    $kontrakLantai = Kontrak::with('unit')
                        ->where('status', 'aktif')
                        ->whereHas('unit', function($q) use ($blok, $range) {
                            $q->where('kode_unit', 'LIKE', $blok . '%')
                              ->whereRaw('CAST(SUBSTRING(kode_unit, 2) AS UNSIGNED) >= ?', [$range['start']])
                              ->whereRaw('CAST(SUBSTRING(kode_unit, 2) AS UNSIGNED) <= ?', [$range['end']]);
                        })
                        ->get();
                    
                    $potensiLantai = 0;
                    foreach ($kontrakLantai as $kontrak) {
                        $potensiLantai += $this->calculateRetribusi($kontrak);
                    }
                    
                    $potensiPerLantai[$lantai] = [
                        'potensi' => $potensiLantai,
                        'terisi' => $kontrakLantai->count()
                    ];
                }
            }
            
            $unitStatistik[$blok] = [
                'total' => $totalUnits[$blok],
                'terisi' => $terisi,
                'kosong' => $totalUnits[$blok] - $terisi,
                'persentase_hunian' => $totalUnits[$blok] > 0 ? round(($terisi / $totalUnits[$blok]) * 100, 1) : 0,
                'potensi' => $retribusiPerBlok[$blok],
                'potensi_per_lantai' => $potensiPerLantai
            ];
        }
        
        $jenisKelaminData = $this->getJenisKelaminData();
        $kelompokUmurData = $this->getKelompokUmurData();

        return view('pages.dashboard', compact(
            'penghuniAktif',
            'unitTerisi',
            'totalUnitSeharusnya',
            'potensiRetribusiBulanIni',
            'potensiRetribusiBulanLalu',
            'potensiRetribusiBulanDepan',
            'perubahanRetribusi',
            'perubahanBulanDepan',
            'retribusiPerBlok',
            'kontrakAkanBerakhir',
            'kontrakExpired',
            'unitStatistik',
            'kontrakDiperbarui',
            'kontrakDiperbaruiCount',
            'penghuniTerbaru',
            'jenisKelaminData',
            'kelompokUmurData',
            'penghuniAktifPeriod',
            'jumlahPenghuniAktifPeriod',
            'filterBulan',
            'filterTahun',
            'tahunOptions'
        ));
    }
}