<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\Kontrak;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function index(Request $request)
    {

        $allPossibleUnits = $this->generateAllPossibleUnits();
        
        $existingUnits = Unit::with(['kontrak' => function($query) {
            $query->where('status', 'aktif')->with('penghuni');
        }])->get()->keyBy('kode_unit');
        
        $units = collect($allPossibleUnits)->map(function($kodeUnit) use ($existingUnits) {
            $unit = $existingUnits->get($kodeUnit);
            $kontrakAktif = $unit?->kontrak->first();
            
            return [
                'kode_unit' => $kodeUnit,
                'blok' => substr($kodeUnit, 0, 1),
                'lantai' => $this->extractLantai($kodeUnit),
                'status' => $unit?->status ?? 'tersedia',
                'penghuni' => $kontrakAktif?->penghuni ?? null,
            ];
        });
        
        if ($request->filled('blok')) {
            $units = $units->filter(function($unit) use ($request) {
                return $unit['blok'] === strtoupper($request->blok);
            });
        }

        if ($request->filled('status')) {
            $units = $units->filter(function($unit) use ($request) {
                return $unit['status'] === $request->status;
            });
        }

        if ($request->filled('search')) {
            $search = strtolower($request->search);
            
            $units = $units->filter(function($unit) use ($search) {
                $kodeUnit = strtolower($unit['kode_unit']);
                if (str_contains($kodeUnit, $search)) {
                    return true;
                }

                if ($unit['penghuni']) {
                    $nama = strtolower($unit['penghuni']->nama ?? '');
                    $nik = strtolower($unit['penghuni']->nik ?? '');
                    
                    return str_contains($nama, $search) || str_contains($nik, $search);
                }
                return false;
            });
        }

        $units = $units->sort(function($a, $b) {
            if ($a['blok'] !== $b['blok']) {
                return strcmp($a['blok'], $b['blok']);
            }
            if ($a['lantai'] !== $b['lantai']) {
                return $a['lantai'] - $b['lantai'];
            }
            return strcmp($a['kode_unit'], $b['kode_unit']);
        });
        
        $perPage = $request->get('per_page', 25);
        $currentPage = $request->get('page', 1);
        $offset = ($currentPage - 1) * $perPage;
        
        $paginatedItems = $units->slice($offset, $perPage)->values();
        
        $units = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedItems,
            $units->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );
        
        return view('unit.index', compact('units'));
    }

    public function show(Request $request, $kode_unit)
    {

        $unit = Unit::where('kode_unit', $kode_unit)->first();

        $riwayatKontrak = collect(); 

        if ($unit) {
            $riwayatKontrak = Kontrak::where('unit_id', $unit->id)
                                ->with('penghuni') 
                                ->orderBy('tanggal_masuk', 'desc')
                                ->get();
        } else {

        }
        
        $lantai = $this->extractLantai($kode_unit);

        return view('unit.show', [
            'kode_unit' => $kode_unit,
            'lantai' => $lantai,
            'unit' => $unit, 
            'riwayatKontrak' => $riwayatKontrak
        ]);
    }

    private function generateAllPossibleUnits()
    {
        $units = [];
        
        foreach (['A', 'B', 'C'] as $blok) {
            for ($unit = 101; $unit <= 103; $unit++) {
                $units[] = "{$blok}{$unit}";
            }

            for ($lantai = 2; $lantai <= 5; $lantai++) {
                for ($nomor = 1; $nomor <= 24; $nomor++) {
                    $units[] = "{$blok}{$lantai}" . str_pad($nomor, 2, '0', STR_PAD_LEFT);
                }
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

    private function extractLantai($kodeUnit)
    {
        $blok = substr($kodeUnit, 0, 1);
        
        if (in_array($blok, ['A', 'B', 'C'])) {
            $unitNum = (int)substr($kodeUnit, 1);
            return (int)floor($unitNum / 100);
        } elseif ($blok === 'D') {
            $unitNum = (int)substr($kodeUnit, 1);
            if ($unitNum >= 101 && $unitNum <= 110) return 1;
            if ($unitNum >= 211 && $unitNum <= 226) return 2;
            if ($unitNum >= 327 && $unitNum <= 342) return 3;
        }
        
        return 0;
    }

    public function getAvailableUnits(Request $request)
    {
        $blok = $request->get('blok');
        
        $allUnits = $this->generateAllUnits($blok);

        $existingUnits = Unit::where('kode_unit', 'LIKE', $blok . '%')
                            ->pluck('id', 'kode_unit')
                            ->toArray();
        
        $occupiedUnitIds = Kontrak::where('status', 'aktif')
                                ->pluck('unit_id')
                                ->toArray();

        $formattedUnits = collect($allUnits)->map(function($kodeUnit) use ($existingUnits, $occupiedUnitIds) {
            $unitId = $existingUnits[$kodeUnit] ?? null;
            $isOccupied = $unitId && in_array($unitId, $occupiedUnitIds);
            
            return [
                'kode_unit' => $kodeUnit,
                'is_available' => !$isOccupied,
                'exists_in_db' => $unitId !== null
            ];
        });
        
        return response()->json([
            'success' => true,
            'units' => $formattedUnits
        ]);
    }
    
    private function generateAllUnits($blok)
    {
        $units = [];
        
        if (in_array($blok, ['A', 'B', 'C'])) {
            for ($unit = 101; $unit <= 103; $unit++) {
                $units[] = "{$blok}{$unit}";
            }
            
            for ($lantai = 2; $lantai <= 5; $lantai++) {
                for ($nomor = 1; $nomor <= 24; $nomor++) {
                    $units[] = "{$blok}{$lantai}" . str_pad($nomor, 2, '0', STR_PAD_LEFT);
                }
            }
        } elseif ($blok === 'D') {

            for ($unit = 101; $unit <= 110; $unit++) {
                $units[] = "D{$unit}";
            }

            for ($unit = 211; $unit <= 226; $unit++) {
                $units[] = "D{$unit}";
            }
            for ($unit = 327; $unit <= 342; $unit++) {
                $units[] = "D{$unit}";
            }
        }
        
        return $units;
    }

    public function checkActiveContract(Request $request)
    {
        $penghuniId = $request->get('penghuni_id');
        
        $activeContract = Kontrak::where('penghuni_id', $penghuniId)
                                ->where('status', 'aktif')
                                ->with('unit')
                                ->first();
        
        return response()->json([
            'has_active_contract' => $activeContract !== null,
            'contract' => $activeContract ? [
                'id' => $activeContract->id,
                'unit_code' => $activeContract->unit->kode_unit ?? '-',
                'tanggal_masuk' => $activeContract->tanggal_masuk->format('d M Y'),
                'tanggal_keluar' => $activeContract->tanggal_keluar ? $activeContract->tanggal_keluar->format('d M Y') : '-'
            ] : null
        ]);
    }
}