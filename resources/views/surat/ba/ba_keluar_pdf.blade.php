<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Surat BA Keluar {{ $penghuni->nama }}</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        @page {
            margin: 1.8cm 2.5cm;
        }
        .page-break {
            page-break-after: always;
        }
        .container {
            width: 100%;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
        }
        .header .logo-kiri {
            max-height: 75px;
            float: left;
            margin-right: 12px;
        }
        .kop-surat-teks {
            text-align: center;
        }
        .header h1, .header h2, .header p {
            margin: 0;
            line-height: 1.2;
        }
        .header h1 { 
            font-size: 14pt; 
            font-weight: bold; 
        }
        .header h2 { 
            font-size: 16pt; 
            font-weight: bold; 
        }
        .header p { 
            font-size: 12pt; 
        }
        .clear { clear: both; }
        .line-divider {
            border-bottom: 3px solid black;
            margin-top: 8px;
            margin-bottom: 15px;
        }
   
        .title {
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            text-decoration: underline;
            margin-top: 15px;
            margin-bottom: 4px;
        }
        .title-no-underline {
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            margin-top: 15px;
            margin-bottom: 4px;
        }
        .subtitle {
            text-align: center;
            font-size: 12pt;
            margin-bottom: 15px;
        }
     
        .content {
            margin-top: 12px;
            text-align: justify;
        }
        .content p {
            margin-bottom: 8px;
        }

        .data-table {
            width: 100%;
            margin-top: 8px;
            margin-bottom: 8px;
            border-collapse: collapse;
        }
        .data-table td {
            padding: 1px 0;
            vertical-align: top;
        }
        .data-table td:first-child {
            width: 170px;
        }
        .data-table td:nth-child(2) {
            width: 10px;
        }

        .table-data-with-no {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            margin-bottom: 15px;
            font-size: 12pt;
        }
        .table-data-with-no th, .table-data-with-no td {
            border: 1px solid black;
            padding: 8px 4px;
            text-align: left;
            vertical-align: top;
        }
        .table-data-with-no th {
            text-align: center;
            font-size: 12pt;
        }
        .table-data-with-no th:first-child, .table-data-with-no td:first-child { 
            width: 30px; 
            text-align: center; 
        }
        .table-data-with-no th:nth-child(3), .table-data-with-no td:nth-child(3) { 
            text-align: center;
            font-size: 11pt;
        }
        .table-data-with-no th:last-child, .table-data-with-no td:last-child { 
            width: 120px;
            text-align: center;
            padding-top: 12px;
            padding-bottom: 12px;
            font-size: 11pt;
        }
        
        .table-tunggakan {
            width: 100%;
            margin-top: 8px;
            margin-bottom: 8px;
            border-collapse: collapse;
        }
        .table-tunggakan td { 
            padding: 2px 3px; 
            vertical-align: top; 
        }
        .table-tunggakan td:first-child { 
            width: 190px; 
        }
        .table-tunggakan td:nth-child(2) { 
            width: 10px; 
        }
        .table-tunggakan td:nth-child(3) { 
            width: 30px; 
        }
        .table-tunggakan td:nth-child(4) { 
            width: 100px; 
            text-align: right; 
        }
     
        .signature-block {
            width: 350px;
            margin-left: auto;
            margin-top: 25px;
        }
        .signature-block p { 
            margin: 0; 
            line-height: 1.3;
        }
        .signature-block .tanggal {
            text-align: left;
            padding-left: 100px;
        }
        .signature-block .jabatan {
            text-align: left;
            padding-left: 125px;
        }
        .signature-name {
            margin-top: 55px;
            font-weight: bold;
            text-decoration: underline;
            padding-left: 60px;
            text-align: center;
        }
     
        .signature-kepala-dinas {
            width: 350px;
            margin-left: auto;
            margin-top: 25px;
        }
        .signature-kepala-dinas p {
            margin: 0;
            line-height: 1.3;
        }

        .signature-kepala-dinas .jabatan-line {
            text-align: center;
        }
        .signature-kepala-dinas .nama-kepala {
            margin-top: 55px;
            font-weight: bold;
            text-decoration: underline;
            text-align: center;
        }
        .signature-kepala-dinas .detail-kepala {
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="header">
            <img src="{{ public_path('images/disperkimkota.png') }}" class="logo-kiri" alt="Logo Pemkot">
            <div class="kop-surat-teks">
                <h1>PEMERINTAH KOTA TEGAL</h1>
                <h2>DINAS PERUMAHAN DAN KAWASAN PERMUKIMAN</h2>
                <p>Jalan Ki Gede Sebayu Nomor 12 Kota Tegal</p>
                <p>Telepon (0283) 358165 Faks. (0283) 353673 Kode Pos 52123</p>
            </div>
            <div class="clear"></div>
        </div>
        <div class="line-divider"></div>

        <p class="title">BERITA ACARA PEMUTUSAN PERJANJIAN SEWA MENYEWA</p>
        <p class="title" style="margin-top: 0;">RUMAH SUSUN SEDERHANA SEWA KOTA TEGAL</p>
        <p class="subtitle">Nomor : {{ $form['nomor_ba'] }}</p>

        <div class="content">
            <p>Pada hari ini, {{ $form['tanggal_pemutusan_spelled_out'] }} ( {{ $form['tanggal_pemutusan_numeric'] }} ), telah sepakat dilakukan pemutusan perjanjian sewa-menyewa atas nama :</p>

            <table class="data-table" style="margin-left: 20px;">
                <tr><td>Nama</td><td>:</td><td><strong>{{ $penghuni->nama }}</strong></td></tr>
                <tr><td>BLOK</td><td>:</td><td>{{ $unit->kode_unit }}</td></tr>
                <tr><td>No. KTP</td><td>:</td><td>{{ $penghuni->nik }}</td></tr>
                <tr><td>Pekerjaan</td><td>:</td><td>{{ $penghuni->pekerjaan ?? '-' }}</td></tr>
                <tr><td>No. S.I.P</td><td>:</td><td>{{ $kontrak->no_sip ?? '-' }}</td></tr>
                <tr><td>No. Perjanjian Sewa</td><td>:</td><td>{{ $kontrak->no_sps ?? '-' }}</td></tr>
            </table>

            <p>Karena yang bersangkutan menyatakan mundur {{ $form['alasan_keluar'] }}.</p>
            <p>Demikian Berita Acara ini dibuat dengan sesungguhnya dan penuh rasa tanggung jawab.</p>

            <table class="table-data-with-no">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Nama</th>
                        <th>Jabatan</th>
                        <th>Tanda Tangan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($staff_list as $index => $s)
                    <tr>
                        <td>{{ $index + 1 }}.</td>
                        <td>{{ $s->nama }}</td>
                        <td>{{ $s->jabatan }}</td>
                        <td></td>
                    </tr>
                    @endforeach
                    <tr>
                        <td>{{ $staff_list->count() + 1 }}.</td>
                        <td>{{ $penghuni->nama }}</td>
                        <td>Penghuni Rusunawa Blok {{ $unit->kode_unit }}</td>
                        <td></td>
                    </tr>
                </tbody>
            </table>

            <div class="signature-kepala-dinas">
                <p class="jabatan-line">KEPALA DINAS PERUMAHAN DAN</p>
                <p class="jabatan-line">KAWASAN PERMUKIMAN KOTA TEGAL</p>
                <br><br><br><br><br>
                <p class="nama-kepala">{{ $kepala_dinas_nama }}</p>
                <p class="detail-kepala">{{ $kepala_dinas_pangkat }}</p>
                <p class="detail-kepala">NIP. {{ $kepala_dinas_nip }}</p>
            </div>
        </div>
    </div>

    <div class="page-break"></div>

    <div class="container">
        <p class="title-no-underline" style="margin-top: 30px; margin-bottom: 35px;">TANDA TERIMA UANG JAMINAN<br>KEPADA PENGHUNI YANG KELUAR DARI HUNIAN RUSUNAWA</p>

        <div class="content">
            <p>Yang bertanda tangan di bawah ini :</p>
            
            <table class="data-table" style="margin-left: 20px;">
                <tr><td>Nama</td><td>:</td><td>{{ $penghuni->nama }}</td></tr>
                <tr><td>BLOK</td><td>:</td><td>{{ $unit->kode_unit }}</td></tr>
                <tr><td>No. KTP</td><td>:</td><td>{{ $penghuni->nik }}</td></tr>
                <tr><td>Alamat KTP</td><td>:</td><td>{{ $penghuni->alamat_ktp ?? '-' }}</td></tr>
            </table>

            <p>
                Telah menerima kembali uang jaminan yang pernah disetorkan dikarenakan keluar dari
                hunian Rusunawa sebesar Rp {{ number_format($nilai_jaminan, 0, ',', '.') }}
                @if($nilai_jaminan == 0)
                    ( Nol Rupiah )
                @else
                    ( {{ ucwords(trim($jaminan_terbilang)) }} Rupiah )
                @endif
            </p>

            <div class="signature-block">
                <p class="tanggal">Tegal, {{ $form['tanggal_ba'] }}</p>
                <p class="jabatan">Penerima</p>
                <br><br><br><br><br>
                <p class="signature-name">{{ $penghuni->nama }}</p>
            </div>
        </div>
    </div>

    <div class="page-break"></div>

    <div class="container">
        <p class="title-no-underline" style="margin-top: 30px; margin-bottom: 35px;">SURAT PERNYATAAN PEMBAYARAN TUNGGAKAN RUSUNAWA</p>

        <div class="content">
            <p>Yang bertanda tangan di bawah ini :</p>
            
            <table class="data-table" style="margin-left: 20px;">
                <tr><td>Nama</td><td>:</td><td>{{ $penghuni->nama }}</td></tr>
                <tr><td>BLOK</td><td>:</td><td>{{ $unit->kode_unit }}</td></tr>
                <tr><td>No. KTP</td><td>:</td><td>{{ $penghuni->nik }}</td></tr>
                <tr><td>Alamat KTP</td><td>:</td><td>{{ $penghuni->alamat_ktp ?? '-' }}</td></tr>
            </table>

            <p>Menyatakan dengan penuh kesadaran bahwa saya mengakui masih memiliki tunggakan pembayaran hunian Rusunawa dengan rincian sebagai berikut :</p>
            
            <table class="table-tunggakan" style="margin-left: 20px;">
                <tr>
                    <td>A. Sewa Bulanan</td><td>:</td>
                    <td>Rp.</td>
                    <td>{{ number_format($form['tunggakan_sewa'], 0, ',', '.') }}</td>
                    <td>{{ $form['periode_tunggakan_sewa'] ? '(' . $form['periode_tunggakan_sewa'] . ')' : '' }}</td>
                </tr>
                <tr>
                    <td>B. Denda</td><td>:</td>
                    <td>Rp.</td>
                    <td>{{ number_format($form['tunggakan_denda'], 0, ',', '.') }}</td>
                    <td>{{ $form['periode_tunggakan_denda'] ? '(' . $form['periode_tunggakan_denda'] . ')' : '' }}</td>
                </tr>
                <tr>
                    <td>C. Biaya Pemakaian Air</td><td>:</td>
                    <td>Rp.</td>
                    <td>{{ number_format($form['tunggakan_air'], 0, ',', '.') }}</td>
                    <td></td>
                </tr>
                <tr>
                    <td>D. Biaya Pemakaian Listrik</td><td>:</td>
                    <td>Rp.</td>
                    <td>{{ number_format($form['tunggakan_listrik'], 0, ',', '.') }}</td>
                    <td></td>
                </tr>
            </table>

            <table class="table-tunggakan" style="margin-left: 20px; margin-top: 12px; border-top: 1px solid #999;">
                <tr>
                    <td>Jumlah Tunggakan</td><td>:</td>
                    <td>Rp.</td>
                    <td style="font-weight: bold;">{{ number_format($jumlah_tunggakan, 0, ',', '.') }}</td>
                    <td></td>
                </tr>
                <tr>
                    <td>Uang Jaminan</td><td>:</td>
                    <td>Rp.</td>
                    <td>{{ number_format($nilai_jaminan, 0, ',', '.') }}</td>
                    <td></td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">Sisa Tunggakan</td><td>:</td>
                    <td style="font-weight: bold;">Rp.</td>
                    <td style="font-weight: bold;">{{ number_format($sisa_tunggakan, 0, ',', '.') }}</td>
                    <td></td>
                </tr>
            </table>

            <p style="margin-top: 15px;">
                Atas tunggakan tersebut, saya bersedia melunasi tunggakan tersebut pada tanggal 
                {{ !empty(trim($form['tanggal_pelunasan'] ?? '')) ? $form['tanggal_pelunasan'] : '' }}
            </p>
            <p>Apabila sampai dengan batas waktu yang telah ditetapkan Saya tidak dapat melunasi tunggakan tersebut, saya bersedia menerima sanksi dan masuk dalam daftar hitam calon penghuni Rusunawa di Kota Tegal di kemudian hari.</p>
            <p>Demikian Surat Pernyataan ini Saya buat dengan sebenar-benarnya.</p>

            <div class="signature-block">
                <p class="tanggal">Tegal, {{ $form['tanggal_ba'] }}</p>
                <p class="jabatan">Yang Menyatakan,</p>
                <br><br><br><br><br>
                <p class="signature-name">{{ $penghuni->nama }}</p>
            </div>
        </div>
    </div>
</body>
</html>