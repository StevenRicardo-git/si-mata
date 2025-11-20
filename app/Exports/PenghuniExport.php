<?php

namespace App\Exports;

use App\Models\Penghuni;
use App\Models\Blacklist;
use App\Models\Unit;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class PenghuniExport implements WithMultipleSheets
{
    protected $request;
    
    public function __construct($request)
    {
        $this->request = $request;
    }

    public function sheets(): array
    {
        $sheets = [];
        
        $hasFilter = ($this->request->has('kontrak_berakhir') && $this->request->kontrak_berakhir != '') ||
                     ($this->request->has('bulan_berakhir') && $this->request->bulan_berakhir != '') ||
                     ($this->request->has('tahun_berakhir') && $this->request->tahun_berakhir != '') ||
                     ($this->request->has('status') && $this->request->status != '') ||
                     ($this->request->has('blok') && $this->request->blok != '') ||
                     ($this->request->has('search') && $this->request->search != '');
        
        if (!$hasFilter) {
            $sheets[] = new PenghuniPerBlokSheet($this->request, 'A');
            $sheets[] = new PenghuniPerBlokSheet($this->request, 'B');
            $sheets[] = new PenghuniPerBlokSheet($this->request, 'C');
            $sheets[] = new PenghuniPerBlokSheet($this->request, 'D');
        } else {
            $sheets[] = new PenghuniPerBlokSheet($this->request, null);
        }
        
        return $sheets;
    }
}

class PenghuniPerBlokSheet implements FromCollection, WithTitle, WithHeadings, WithStyles, WithColumnWidths, WithColumnFormatting
{
    protected $request;
    protected $blok;
    
    public function __construct($request, $blok = null)
    {
        $this->request = $request;
        $this->blok = $blok;
    }

    public function title(): string
    {
        return $this->blok ? "Blok {$this->blok}" : "Data Penghuni";
    }

    public function headings(): array
    {
        return [
            'NO BLOK',
            'Lt',
            'NAMA',
            'MASA SEWA MASUK',
            'MASA SEWA KELUAR',
            'KERINGANAN',
            'NO. SIP',
            'TANGGAL SIP',
            'NO. SPS',
            'TANGGAL SPS',
            'TTL',
            'PEKERJAAN',
            'NO. KTP',
            'ALAMAT KTP',
            'Nama 1', 'NIK', 'Umur', 'Hubungan',
            'Nama 2', 'Umur', 'Hubungan',
            'Nama 3', 'Umur', 'Hubungan',
            'Nama 4', 'Umur', 'Hubungan',
            'Nama 5', 'Umur', 'Hubungan',
            'Nama 6', 'Umur', 'Hubungan',
            'Nilai Jaminan'
        ];
    }

    public function collection(): Collection
    {
        $hasFilter = $this->hasActiveFilter();
        $penghuniData = $this->getPenghuniData();
        $result = [];
        
        if ($hasFilter) {
            foreach ($penghuniData as $kodeUnit => $penghuni) {
                $result[] = $this->formatPenghuniRow($penghuni, $kodeUnit);
            }
        } else {
            $allUnits = $this->generateAllUnits();
            
            foreach ($allUnits as $unit) {
                $penghuni = $penghuniData->get($unit);
                
                if ($penghuni) {
                    $result[] = $this->formatPenghuniRow($penghuni, $unit);
                } else {
                    $result[] = $this->formatEmptyRow($unit);
                }
            }
        }
        
        return collect($result);
    }
    
    protected function hasActiveFilter()
    {
        return ($this->request->has('kontrak_berakhir') && $this->request->kontrak_berakhir != '') ||
               ($this->request->has('bulan_berakhir') && $this->request->bulan_berakhir != '') ||
               ($this->request->has('tahun_berakhir') && $this->request->tahun_berakhir != '') ||
               ($this->request->has('status') && $this->request->status != '') ||
               ($this->request->has('blok') && $this->request->blok != '') ||
               ($this->request->has('search') && $this->request->search != '');
    }

    protected function generateAllUnits()
    {
        $units = [];
        
        if ($this->blok) {
            if (in_array($this->blok, ['A', 'B', 'C'])) {
                for ($lantai = 1; $lantai <= 5; $lantai++) {
                    if ($lantai == 1) {
                        for ($nomor = 1; $nomor <= 3; $nomor++) {
                            $units[] = $this->blok . $lantai . str_pad($nomor, 2, '0', STR_PAD_LEFT);
                        }
                    } else {
                        for ($nomor = 1; $nomor <= 24; $nomor++) {
                            $units[] = $this->blok . $lantai . str_pad($nomor, 2, '0', STR_PAD_LEFT);
                        }
                    }
                }
            } elseif ($this->blok === 'D') {
                for ($i = 101; $i <= 110; $i++) {
                    $units[] = 'D' . $i;
                }
                for ($i = 211; $i <= 226; $i++) {
                    $units[] = 'D' . $i;
                }
                for ($i = 327; $i <= 342; $i++) {
                    $units[] = 'D' . $i;
                }
            }
        }
        
        return $units;
    }

    protected function getPenghuniData()
    {
        $query = Penghuni::query();
        
        $blacklistedNiks = Blacklist::where('status', 'blacklist')->pluck('nik');
        $query->whereNotIn('nik', $blacklistedNiks);

        if ($this->blok && !$this->hasActiveFilter()) {
            $query->whereHas('kontrak', function($q) {
                $q->where('status', 'aktif')
                ->whereHas('unit', function($q2) {
                    $q2->where('kode_unit', 'LIKE', $this->blok . '%');
                });
            });
        }

        if ($this->request->has('kontrak_berakhir') && $this->request->kontrak_berakhir != '') {
            $days = (int)$this->request->kontrak_berakhir;
            $today = Carbon::today();
            $endDate = $today->copy()->addDays($days);
            
            $query->whereHas('kontrak', function($q) use ($today, $endDate) {
                $q->where('status', 'aktif')
                  ->whereBetween('tanggal_keluar', [$today, $endDate]);
            });
        }

        if ($this->request->has('bulan_berakhir') && $this->request->bulan_berakhir != '') {
            $bulan = (int)$this->request->bulan_berakhir;
            $tahun = now()->year;
            
            $query->whereHas('kontrak', function($q) use ($bulan, $tahun) {
                $q->where('status', 'aktif')
                  ->whereYear('tanggal_keluar', $tahun)
                  ->whereMonth('tanggal_keluar', $bulan);
            });
        }

        if ($this->request->has('tahun_berakhir') && $this->request->tahun_berakhir != '') {
            $tahun = (int)$this->request->tahun_berakhir;
            
            $query->whereHas('kontrak', function($q) use ($tahun) {
                $q->where('status', 'aktif')
                  ->whereYear('tanggal_keluar', $tahun);
            });
        }

        if ($this->request->has('status') && $this->request->status != '') {
            if ($this->request->status == 'aktif') {
                $query->whereHas('kontrak', function($q) {
                    $q->where('status', 'aktif');
                });
            } elseif ($this->request->status == 'tidak_aktif') {
                $query->whereDoesntHave('kontrak', function($q) {
                    $q->where('status', 'aktif');
                });
            }
        }

        if ($this->request->has('blok') && $this->request->blok != '') {
            $blok = strtoupper($this->request->blok);
            $query->whereHas('kontrak', function($q) use ($blok) {
                $q->where('status', 'aktif')
                ->whereHas('unit', function($q2) use ($blok) {
                    $q2->where('kode_unit', 'LIKE', $blok . '%');
                });
            });
        }

        if ($this->request->has('search') && $this->request->search != '') {
            $search = $this->request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama', 'LIKE', "%{$search}%")
                ->orWhere('nik', 'LIKE', "%{$search}%");
            });
        }

        $query->with(['kontrak' => function($q) {
            $q->with('unit')->latest('tanggal_masuk')->limit(1);
        }, 'keluarga']);

        $penghuni = $query->get();
        
        $mapped = [];
        foreach ($penghuni as $p) {
            $kontrak = $p->kontrak;
            if ($kontrak && $kontrak->unit) {
                $mapped[$kontrak->unit->kode_unit] = $p;
            }
        }
        
        return collect($mapped);
    }

    protected function formatPenghuniRow($penghuni, $kodeUnit)
    {
        $kontrak = $penghuni->kontrak;
        $lantai = $this->getLantaiFromUnit($kodeUnit);

        $ttl = '';
        if ($penghuni->tempat_lahir) {
            $ttl .= $penghuni->tempat_lahir;
        }
        if ($penghuni->tanggal_lahir) {
            if ($ttl) $ttl .= ', ';
            $ttl .= Carbon::parse($penghuni->tanggal_lahir)->locale('id')->translatedFormat('d F Y');
        }

        $keluarga = $penghuni->keluarga;
        $pasangan = $keluarga->whereIn('hubungan', ['istri', 'suami'])->first();
        $anak = $keluarga->where('hubungan', 'anak')->take(5)->values();

        $row = [
            $kodeUnit,
            $lantai,
            strtoupper($penghuni->nama),
            $kontrak && $kontrak->tanggal_masuk ? Carbon::parse($kontrak->tanggal_masuk)->format('d F Y') : '',
            $kontrak && $kontrak->tanggal_keluar ? Carbon::parse($kontrak->tanggal_keluar)->format('d F Y') : '',
            $kontrak ? strtoupper($kontrak->keringanan) : '',
            $kontrak ? ($kontrak->no_sip ?? '') : '',
            $kontrak && $kontrak->tanggal_sip ? Carbon::parse($kontrak->tanggal_sip)->format('d F Y') : '',
            $kontrak ? ($kontrak->no_sps ?? '') : '',
            $kontrak && $kontrak->tanggal_sps ? Carbon::parse($kontrak->tanggal_sps)->format('d F Y') : '',
            $ttl,
            $penghuni->pekerjaan ?? '',
            "'" . $penghuni->nik,
            $penghuni->alamat_ktp ?? '',
        ];

        if ($pasangan) {
            $row[] = strtoupper($pasangan->nama);
            $row[] = $pasangan->nik ? "'" . $pasangan->nik : '';
            $row[] = $pasangan->umur ?? '';
            $row[] = strtoupper($pasangan->hubungan);
        } else {
            $row[] = '';
            $row[] = '';
            $row[] = '';
            $row[] = ''; 
        }

        for ($i = 0; $i < 5; $i++) {
            if (isset($anak[$i])) {
                $k = $anak[$i];
                $row[] = strtoupper($k->nama);
                $row[] = $k->umur ?? '';
                $row[] = strtoupper($k->hubungan);
            } else {
                $row[] = '';
                $row[] = '';
                $row[] = ''; 
            }
        }
        
        $row[] = $kontrak && $kontrak->nilai_jaminan ? $kontrak->nilai_jaminan : '';

        return $row;
    }

    protected function formatEmptyRow($kodeUnit)
    {
        $lantai = $this->getLantaiFromUnit($kodeUnit);
        
        $row = [
            $kodeUnit,
            $lantai,
            '', '', '', '', '', '', '', '', '', '', '', '',
        ];
        
        for ($i = 0; $i < 4; $i++) {
            $row[] = '';
        }
        
        for ($i = 0; $i < 15; $i++) {
            $row[] = '';
        }
        
        $row[] = '';
        
        return $row;
    }

    protected function getLantaiFromUnit($kodeUnit)
    {
        if (!$kodeUnit || strlen($kodeUnit) < 2) return '';
        
        $blok = substr($kodeUnit, 0, 1);
        
        if (in_array($blok, ['A', 'B', 'C'])) {
            return (int)substr($kodeUnit, 1, 1);
        } elseif ($blok === 'D') {
            $unitNum = (int)substr($kodeUnit, 1);
            if ($unitNum >= 101 && $unitNum <= 110) return 1;
            if ($unitNum >= 211 && $unitNum <= 226) return 2;
            if ($unitNum >= 327 && $unitNum <= 342) return 3;
        }
        
        return '';
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        
        $sheet->getStyle('A1:' . $highestColumn . '1')->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFFF00']
            ],
            'font' => [
                'bold' => true,
                'size' => 11
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true
            ]
        ]);

        $sheet->getStyle('A2:' . $highestColumn . $highestRow)->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true
            ]
        ]);

        $sheet->getStyle('C2:C' . $highestRow)->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true
            ]
        ]);
        
        $sheet->getStyle('N2:N' . $highestRow)->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true
            ]
        ]);
        
        foreach (['D', 'E', 'H', 'J'] as $col) {
            $sheet->getStyle($col . '2:' . $col . $highestRow)->applyFromArray([
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true
                ]
            ]);
        }
        $sheet->freezePane('G2');

        $colors = [
            'C5CAE9',
            'FFCCBC',
            'C8E6C9',
            'F8BBD0',
        ];
        
        $startCol = 15;
        $colIndex = 0;
        
        for ($j = 0; $j < 4; $j++) {
            $col = $this->getColumnLetter($startCol + $colIndex);
            $sheet->getStyle($col . '1')->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => $colors[0]]
                ]
            ]);
            $colIndex++;
        }
        
        for ($i = 0; $i < 5; $i++) {
            $colorIndex = 1 + floor($i / 2);
            if ($colorIndex >= count($colors)) {
                $colorIndex = count($colors) - 1;
            }
            
            for ($j = 0; $j < 3; $j++) {
                $col = $this->getColumnLetter($startCol + $colIndex);
                $sheet->getStyle($col . '1')->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => $colors[$colorIndex]]
                    ]
                ]);
                $colIndex++;
            }
        }

        return [];
    }

    protected function getColumnLetter($columnIndex)
    {
        $letter = '';
        while ($columnIndex > 0) {
            $columnIndex--;
            $letter = chr(65 + ($columnIndex % 26)) . $letter;
            $columnIndex = floor($columnIndex / 26);
        }
        return $letter;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 12,
            'B' => 5,
            'C' => 25,
            'D' => 18,
            'E' => 18,
            'F' => 12,
            'G' => 15,
            'H' => 18,
            'I' => 15,
            'J' => 18,
            'K' => 25,
            'L' => 20,
            'M' => 18,
            'N' => 35,
            'O' => 25,
            'P' => 18,
            'Q' => 8, 
            'R' => 12,
            'S' => 25,
            'T' => 8, 
            'U' => 12,  
            'V' => 25,
            'W' => 8, 
            'X' => 12,  
            'Y' => 25,
            'Z' => 8,
            'AA' => 12, 
            'AB' => 25,
            'AC' => 8,
            'AD' => 12, 
            'AE' => 25,
            'AF' => 8,
            'AG' => 12,
            'AH' => 15, 
        ];
    }

    public function columnFormats(): array
    {
        return [
            'M' => NumberFormat::FORMAT_TEXT,
            'P' => NumberFormat::FORMAT_TEXT,
        ];
    }
}