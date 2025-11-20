<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $judul }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            font-size: 8pt;
            line-height: 1.3;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 3px solid #2563eb;
        }
        
        .header h1 {
            font-size: 14pt;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 4px;
        }
        
        .header .subtitle {
            font-size: 9pt;
            color: #666;
            margin-bottom: 2px;
        }
        
        .header .tanggal {
            font-size: 7pt;
            color: #999;
        }
        
        .filter-info {
            background-color: #eff6ff;
            border: 1px solid #93c5fd;
            border-radius: 4px;
            padding: 6px 10px;
            margin-bottom: 12px;
        }
        
        .filter-info strong {
            color: #1e40af;
            font-size: 8pt;
        }
        
        .filter-info span {
            color: #1f2937;
            font-size: 7pt;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        
        table thead {
            background-color: #1e40af;
            color: white;
        }
        
        table thead th {
            padding: 6px 3px;
            text-align: center;
            font-size: 7pt;
            font-weight: bold;
            border: 1px solid #1e3a8a;
        }

        table tfoot td {
            background-color: #1e40af;
            color: white;
            font-weight: bold;
            padding: 6px 3px;
            font-size: 7pt;
            border: 1px solid #1e3a8a;
            text-align: center;
        }
        
        table tbody td {
            padding: 5px 3px;
            border: 1px solid #d1d5db;
            font-size: 7pt;
            vertical-align: middle;
            text-align: center;
        }
        
        table tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }
        
        table tbody tr.bulan-januari { background-color: #dbeafe !important; }
        table tbody tr.bulan-februari { background-color: #d1fae5 !important; }
        table tbody tr.bulan-maret { background-color: #fef3c7 !important; }
        table tbody tr.bulan-april { background-color: #ffe4e6 !important; }
        table tbody tr.bulan-mei { background-color: #e0e7ff !important; }
        table tbody tr.bulan-juni { background-color: #fce7f3 !important; }
        table tbody tr.bulan-juli { background-color: #ddd6fe !important; }
        table tbody tr.bulan-agustus { background-color: #fef9c3 !important; }
        table tbody tr.bulan-september { background-color: #ccfbf1 !important; }
        table tbody tr.bulan-oktober { background-color: #fed7aa !important; }
        table tbody tr.bulan-november { background-color: #fbcfe8 !important; }
        table tbody tr.bulan-desember { background-color: #bfdbfe !important; }
        
        .text-bold {
            font-weight: bold;
        }
        
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 6pt;
            font-weight: bold;
        }
        
        .badge-aktif {
            background-color: #d1fae5;
            color: #065f46;
        }
        
        .badge-tidak-aktif {
            background-color: #f3f4f6;
            color: #374151;
        }
        
        .badge-dapat {
            background-color: #dbeafe;
            color: #1e40af;
        }
        
        .badge-tidak {
            background-color: #fee2e2;
            color: #991b1b;
        }
        
        .badge-normal {
            background-color: #f3f4f6;
            color: #4b5563;
        }
        
        .footer {
            margin-top: 15px;
            padding-top: 8px;
            border-top: 1px solid #d1d5db;
            text-align: center;
            font-size: 6pt;
            color: #999;
        }
        
        .no-data {
            text-align: center;
            padding: 30px;
            color: #6b7280;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $judul }}</h1>
        <div class="subtitle">Sistem Manajemen Rusunawa Kota Tegal</div>
        <div class="tanggal">Dicetak pada: {{ $tanggalCetak }}</div>
    </div>
    
    @if(count($filterInfo) > 0)
    <div class="filter-info">
        <strong>Filter Aktif:</strong> 
        <span>{{ implode(' | ', $filterInfo) }}</span>
    </div>
    @endif
    
    @if($penghuni->count() > 0)
    <table>
        <thead>
            <tr>
                <th rowspan="2" style="width: 3%;">No</th>
                <th rowspan="2" style="width: 5%;">Unit</th>
                <th rowspan="2" style="width: 12%;">Nama</th>
                <th rowspan="2" style="width: 3%;">JK</th>
                <th rowspan="2" style="width: 9%;">NIK</th>
                <th colspan="2" style="width: 14%;">Masa Hunian</th>
                <th rowspan="2" style="width: 6%;">Keringanan</th>
                <th colspan="3" style="width: 23%;">Nilai Retribusi</th>
                <th rowspan="2" style="width: 5%;">Status</th>
            </tr>
            <tr>
                <th style="width: 7%;">Awal</th>
                <th style="width: 7%;">Akhir</th>
                <th style="width: 8%;">Sewa</th>
                <th style="width: 8%;">Air</th>
                <th style="width: 7%;">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @php
                $no = 1;
                $totalSewa = 0;
                $totalAir = 0;
            @endphp
            @foreach($penghuni as $p)
                @php
                    $kontrak = $p->kontrak;
                    $kodeUnit = $kontrak && $kontrak->unit ? $kontrak->unit->kode_unit : '-';
                    
                    $nominalSewa = $kontrak ? ($kontrak->calculated_sewa ?? 0) : 0;
                    $tarifAir = $kontrak ? ($kontrak->calculated_air ?? 0) : 0;
                    $totalRetribusi = $nominalSewa + $tarifAir;
                    
                    $totalSewa += $nominalSewa;
                    $totalAir += $tarifAir;

                    $bulanClass = '';
                    if ($isFilterTahun && $kontrak && $kontrak->tanggal_keluar) {
                        $bulan = \Carbon\Carbon::parse($kontrak->tanggal_keluar)->month;
                        $bulanNames = [
                            1 => 'januari', 2 => 'februari', 3 => 'maret', 4 => 'april',
                            5 => 'mei', 6 => 'juni', 7 => 'juli', 8 => 'agustus',
                            9 => 'september', 10 => 'oktober', 11 => 'november', 12 => 'desember'
                        ];
                        $bulanClass = 'bulan-' . $bulanNames[$bulan];
                    }
                    
                    \Carbon\Carbon::setLocale('id');
                @endphp
                <tr class="{{ $bulanClass }}">
                    <td>{{ $no++ }}</td>
                    <td class="text-bold">{{ $kodeUnit }}</td>
                    <td>{{ $p->nama }}</td>
                    <td>
                        @if($p->jenis_kelamin == 'laki-laki')
                            L
                        @elseif($p->jenis_kelamin == 'perempuan')
                            P
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $p->nik }}</td>
                    <td>
                        @if($kontrak && $kontrak->tanggal_masuk)
                            {{ \Carbon\Carbon::parse($kontrak->tanggal_masuk)->translatedFormat('d M Y') }}
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        @if($kontrak && $kontrak->tanggal_keluar)
                            {{ \Carbon\Carbon::parse($kontrak->tanggal_keluar)->translatedFormat('d M Y') }}
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        @if($kontrak && $kontrak->keringanan)
                            @if($kontrak->keringanan == 'dapat') 
                                <span class="badge badge-dapat">Dapat</span>
                            @elseif($kontrak->keringanan == 'tidak') 
                                <span class="badge badge-tidak">Tidak</span>
                            @else 
                                <span class="badge badge-normal">Normal</span>
                            @endif
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $nominalSewa > 0 ? 'Rp ' . number_format($nominalSewa, 0, ',', '.') : '-' }}</td>
                    <td>{{ $tarifAir > 0 ? 'Rp ' . number_format($tarifAir, 0, ',', '.') : '-' }}</td>
                    <td class="text-bold">{{ $totalRetribusi > 0 ? 'Rp ' . number_format($totalRetribusi, 0, ',', '.') : '-' }}</td>
                    <td>
                        @if($kontrak && $kontrak->status == 'aktif')
                            <span class="badge badge-aktif">Aktif</span>
                        @else
                            <span class="badge badge-tidak-aktif">Tidak Aktif</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>

        @if(!in_array('Status: Tidak Aktif', $filterInfo))
        <tfoot>
            <tr>
                <td colspan="2">
                    Total Data: {{ $penghuni->count() }} penghuni
                </td>
                <td colspan="2">
                    Total Potensi Pendapatan
                </td>
                <td>
                    @php
                        $grandTotal = $totalSewa + $totalAir;
                    @endphp
                    Rp {{ number_format($grandTotal, 0, ',', '.') }}
                </td>
                <td></td>
            </tr>
        </tfoot>
        @endif
    </table>

    @else
    <div class="no-data">
        <p>Tidak ada data penghuni yang sesuai dengan filter yang diterapkan.</p>
    </div>
    @endif
    
    <div class="footer">
        <p>Dokumen ini dicetak secara otomatis dari Sistem Manajemen Rusunawa Kota Tegal</p>
        <p>{{ $tanggalCetak }}</p>
    </div>
</body>
</html>