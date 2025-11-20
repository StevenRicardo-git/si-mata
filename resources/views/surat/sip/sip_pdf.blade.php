<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Surat SIP {{ $penghuni->nama }}</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        @page {
            margin: 2cm 2cm 2cm 2cm;
            size: folio portrait;
        }
        .container {
            width: 100%;
        }
        .page-break {
            page-break-after: always;
        }
        
        .page-wrapper-sip {
            position: relative;
            min-height: 100%;
        }
        .main-content-sip {
            padding-bottom: 100px;
        }
        .header {
            text-align: center;
            margin-bottom: 12px;
            width: 100%;
        }
        .header .logo-kiri {
            max-height: 70px;
            float: left;
            margin-right: 10px;
        }
        .non-italic {
            font-style: normal !important;
        }
        .kop-surat-teks { text-align: center; }
        .header h1 { font-size: 14pt; font-weight: bold; margin: 0; line-height: 1.2; }
        .header h2 { font-size: 16pt; font-weight: bold; margin: 0; line-height: 1.2; }
        .header p { font-size: 12pt; margin: 0; line-height: 1.2; }
        .clear { clear: both; }
        .line-divider {
            border-bottom: 3px solid black;
            margin-top: 6px;
            margin-bottom: 12px;
        }
        .title {
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            text-decoration: underline;
            margin-top: 12px;
            margin-bottom: 0px;
        }
        .subtitle {
            text-align: center;
            font-size: 12pt;
            margin-top: 0px;
            margin-bottom: 12px;
        }
        .content {
            margin-top: 10px;
            text-align: justify;
        }
        .content p { margin-bottom: 6px; }
        
        .data-table {
            width: 100%;
            margin-top: 6px;
            margin-bottom: 6px;
            border-collapse: collapse;
        }
        .data-table td {
            padding: 1px 0;
            vertical-align: top;
        }
        .data-table td.label {
            width: 130px;
            padding-right: 5px;
            white-space: nowrap;
        }
        .data-table td.separator {
            width: 5px;
            text-align: center;
        }
        
        .foto-container {
            width: 100%;
            margin-top: 20px;
            margin-bottom: 4px;
            overflow: auto;
            position: relative;
        }
        .foto-box {
            width: 4cm;
            height: 6cm;
            border: 1px solid black;
            float: left;
        }
        .foto-box:first-child {
            margin-right: 0.3cm;
        }
        
        .signature-section {
            margin-top: 20px;
            width: 100%;
        }

        .signature1-section {
            margin-top: -240px;
            width: 100%;
            position: absolute;
        }

        .signature-box-right {
            width: 50%;
            margin-left: 50%;
            text-align: center;
        }
        .signature-box-right p { margin: 0; line-height: 1.3; }
        .signature-box-right .jabatan { font-weight: normal; }
        .signature-box-right .nama { font-weight: bold; text-decoration: underline; text-transform: uppercase;}
        .signature-box-right .nip { font-weight: normal; }

        .catatan-sip {
            position: absolute;
            bottom: 0;
            width: 100%;
            border-top: 1px solid black;
            padding-top: 8px;
            padding-left: 15px;
            padding-right: 15px;
        }
        .catatan-sip .catatan-title {
            font-size: 9pt;
            font-style: italic;
            font-weight: bold;
            margin-bottom: 6px;
        }
        .catatan-sip ol {
            margin: 0;
            padding-left: 20px;
            font-size: 9pt;
            font-style: italic;
            text-align: justify;
        }
        .catatan-sip li { margin-bottom: 4px; }

        .title-keluarga {
            text-align: center;
            font-weight: bold;
            margin-bottom: 0;
            font-size: 12pt;
        }
        .subtitle-keluarga {
            text-align: center;
            margin-top: 4px;
            font-weight: bold;
            font-size: 11pt;
        }
        .family-table {
            width: 100%;
            border: 2px solid black;
            border-collapse: collapse;
            margin-top: 12px;
            font-size: 11pt;
        }
        .family-table th, .family-table td {
            border: 1px solid black;
            padding: 6px;
            text-align: left;
        }
        .family-table th {
            background-color: #ffffff;
            text-align: center;
            font-weight: bold;
        }
        .family-table td.center { text-align: center; }
        .family-signature {
            margin-top: 30px;
            width: 300px;
            float: right;
            text-align: center;
        }
        .family-signature p { margin: 0; line-height: 1.3; }
        .family-signature .nama {
            margin-top: 50px;
            font-weight: bold;
            text-decoration: underline;
        }

        .sps-container {
            margin-top: 8px;
            text-align: justify;
        }
        .sps-title {
            text-align: center;
            font-size: 12pt;
            margin-top: 12px;
            margin-bottom: 0px;
        }
        .sps-subtitle {
            text-align: center;
            font-size: 12pt;
            margin-top: 0px;
            margin-bottom: 12px;
        }
        .sps-table {
            width: 100%;
            border: none;
            border-collapse: collapse;
        }
        .sps-table td {
            padding: 3px 0px;
            vertical-align: top;
            text-align: justify;
            line-height: 1.4;
        }
        .sps-table td.num {
            width: 25px;
            text-align: right;
            padding-right: 8px;
        }
        .sps-table td.pasal-title {
            text-align: center;
            padding-top: 12px;
            padding-bottom: 8px;
        }
        .sps-signature-section {
            margin-top: 30px;
            width: 100%;
        }
        .sps-signature-box-left {
            width: 50%;
            float: left;
            text-align: center;
        }
        .sps-signature-box-right {
            width: 50%;
            float: right;
            text-align: center;
        }
        .sps-signature-box-left p, .sps-signature-box-right p {
            margin: 0; line-height: 1.3;
        }
        .sps-signature-nama {
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
        }

        .bast-catatan {
            position: absolute;
            bottom: 0;
            width: 100%;
            border-top: 1px solid black;
            padding-top: 8px;
            padding-left: 15px;
            padding-right: 15px;
        }
        .bast-catatan p {
            font-size: 9pt;
            font-style: italic;
            line-height: 1.3;
            text-align: justify;
        }
    </style>
</head>
<body>

    <div class="page-wrapper-sip">
        <div class="main-content-sip">
            <div class="header">
                <img src="{{ public_path('images/disperkimkota.png') }}" class="logo-kiri" alt="Logo Pemkot">
                <div class="kop-surat-teks">
                    <h1>PEMERINTAH KOTA TEGAL</h1>
                    <h2>DINAS PERUMAHAN DAN KAWASAN PERMUKIMAN</h2>
                    <p>Jalan Ki Gede Sebayu Nomor 12 Kota Tegal</p>
                    <p>Telepon (0283) 358165 Faksimile (0283) 353673 Kode Pos 52123</p>
                </div>
                <div class="clear"></div>
            </div>
            <div class="line-divider"></div>

            <p class="title">SURAT IJIN PENGHUNIAN</p>
            <p class="subtitle" style="font-weight: bold; text-decoration: none;">Nomor : {{ $kontrak->no_sip ?? '' }}</p>

            <div class="content">
                <p>Berdasarkan pada Surat Perjanjian Sewa Menyewa Rusunawa Nomor {{ $kontrak->no_sps ?? '' }}, maka kepada tersebut di bawah ini:</p>

                <table class="data-table" style="margin-left: 20px;">
                    <tr>
                        <td class="label">Nama</td>
                        <td class="separator">:&nbsp;</td>
                        <td class="value">{{ $penghuni->nama }}</td>
                    </tr>
                    <tr>
                        <td class="label">Tempat / tgl lahir</td>
                        <td class="separator">:&nbsp;</td>
                        <td class="value">
                            @php
                                $ttl = '';
                                if ($penghuni->tempat_lahir) { $ttl .= $penghuni->tempat_lahir; }
                                if ($penghuni->tanggal_lahir) {
                                    if ($ttl) $ttl .= ', ';
                                    $ttl .= \Carbon\Carbon::parse($penghuni->tanggal_lahir)->translatedFormat('d F Y');
                                }
                            @endphp
                            {{ $ttl ?: '' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="label">Pekerjaan</td>
                        <td class="separator">:&nbsp;</td>
                        <td class="value">{{ $penghuni->pekerjaan ?? '' }}</td>
                    </tr>
                    <tr>
                        <td class="label">No. KTP</td>
                        <td class="separator">:&nbsp;</td>
                        <td class="value">{{ $penghuni->nik }}</td>
                    </tr>
                    <tr>
                        <td class="label">Alamat KTP</td>
                        <td class="separator">:&nbsp;</td>
                        <td class="value">{{ $penghuni->alamat_ktp ?? '' }}</td>
                    </tr>
                </table>

                @php
                    $blok = $unit->kode_unit ? substr($unit->kode_unit, 0, 1) : '-';
                    $lantai = '-';
                    $nomor = preg_replace("/[^0-9]/", "", $unit->kode_unit);

                    if (in_array($blok, ['A', 'B', 'C']) && strlen($nomor) >= 3) {
                        $lantai = substr($nomor, 0, 1);
                    }
                    elseif ($blok == 'D' && strlen($nomor) >= 3) {
                        $lantai = substr($nomor, 0, 1);
                    }
                @endphp
                
                <p style="text-align: justify; margin-top: 12px; margin-bottom: 8px;">
                    Telah diserahkan 1 (satu) set kunci rumah dari 1 (satu) {{ $namaRusun }} Blok {{ $blok }} Lantai {{ $lantai }} Nomor {{ $nomor }} dalam keadaan baik, oleh karena itu yang bersangkutan berhak dan diizinkan Menempati / Menghuni Unit Hunian tersebut terhitung mulai tanggal: 
                    <strong style="font-weight: bold;">
                        {{ $kontrak->tanggal_masuk ? \Carbon\Carbon::parse($kontrak->tanggal_masuk)->translatedFormat('d F Y') : '-' }} 
                        s.d. 
                        {{ $kontrak->tanggal_keluar ? \Carbon\Carbon::parse($kontrak->tanggal_keluar)->translatedFormat('d F Y') : '-' }}
                    </strong>
                </p>

                <div class="foto-container">
                    <div class="foto-box"></div>
                    <div class="foto-box"></div>
                    <div class="clear"></div>
                </div>
                
                <div class="signature1-section">
                    <div class="signature-box-right">
                        <p class="tanggal">Tegal, {{ $tanggal_cetak }}</p>
                        <p>KEPALA DINAS PERUMAHAN DAN</p>
                        <p>KAWASAN PERMUKIMAN KOTA TEGAL</p>
                        <br><br><br><br><br>
                        
                        <p class="nama" style="text-transform: uppercase;">{{ $kepalaDinas->nama }}</p>
                        @if($kepalaDinas->pangkat)
                            <p class="nip">{{ $kepalaDinas->pangkat }}</p>
                        @endif
                        @if($kepalaDinas->nip)
                            <p class="nip">NIP. {{ $kepalaDinas->nip }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="catatan-sip">
            <p class="catatan-title">Catatan:</p>
            <ol>
                <li>Surat Ijin Penghunian (SIP) ini agar disimpan dengan baik dan diperlihatkan apabila Pengelola Rusunawa atau Pihak yang berwajib mendatangi Unit Hunian saudara.</li>
                <li>Penghuni sesuai daftar keluarga/pengikut terlampir.</li>
            </ol>
        </div>
    </div>

    <div class="page-break"></div>
    
    <div class="container">
        <h3 class="title-keluarga">DAFTAR KELUARGA/PENGIKUT RUANG HUNIAN</h3>
        <p class="subtitle-keluarga">
            BLOK {{ $blok }} LANTAI {{ $lantai }} NOMOR {{ $nomor }}
        </p>
        <br>

        <table class="family-table">
            <thead>
                <tr>
                    <th style="width: 8%;">No.</th>
                    <th style="width: 35%;">NAMA</th>
                    <th style="width: 12%;">UMUR (TAHUN)</th>
                    <th style="width: 25%;">HUBUNGAN DENGAN PENYEWA</th>
                    <th style="width: 20%;">KETERANGAN</th>
                </tr>
            </thead>
            <tbody>
                @if($penghuni->keluarga->isNotEmpty())
                    @foreach($penghuni->keluarga as $index => $anggota)
                        <tr>
                            <td class="center">{{ $index + 1 }}</td>
                            <td>{{ $anggota->nama }}</td>
                            <td class="center">{{ $anggota->umur ?? '' }}</td>
                            <td class="center">{{ ucfirst($anggota->hubungan) }}</td>
                            <td class="center">{{ $anggota->catatan ?? '' }}</td>
                        </tr>
                    @endforeach
                @else
                @endif
            </tbody>
        </table>

        <div class="family-signature">
            <p>Tegal, {{ $tanggal_cetak }}</p>
            <p>Calon Penghuni/ Penghuni</p>
            <br><br><br>
            <p class="nama" style="text-transform: uppercase;">{{ $penghuni->nama }}</p>
        </div>
    </div>
    
    @php
        $tglSps = $kontrak->tanggal_sps ?? $kontrak->tanggal_masuk;
        $tanggalSpsLengkap = $tglSps ? \Carbon\Carbon::parse($tglSps)->translatedFormat('l \T\a\n\g\g\a\l d F Y') : '.......';
    @endphp

    <div class="page-break"></div>
    <div class="container sps-container">
        <p class="sps-title">PERJANJIAN SEWA MENYEWA</p>
        <p class="sps-title" style="margin-top: 0;">RUMAH SUSUN SEDERHANA SEWA (RUSUNAWA)</p>
        <p class="sps-subtitle">Nomor: {{ $kontrak->no_sps ?? '600.2.6/54' }}</p>

        <table class="sps-table">
            <tr>
                <td colspan="2" style="padding-bottom: 8px; text-align: justify;">
                    Pada hari ini, {{ $tanggalSpsLengkap }}, yang bertanda tangan di bawah ini:
                </td>
            </tr>
            <tr>
                <td class="num">1.</td>
                <td style="text-align: justify;">
                    {{ $kepalaDinas->nama }}, Jabatan {{ $kepalaDinas->jabatan }}, yang berkedudukan di Jl. Ki Gede Sebayu No. 11 Kota Tegal.
                    <br>Yang selanjutnya disebut <b>PIHAK PERTAMA</b>.
                </td>
            </tr>
            <tr>
                <td class="num">2.</td>
                <td style="text-align: justify;">
                    {{ $penghuni->nama }}, {{ $penghuni->pekerjaan ?? 'Pekerjaan' }}, yang beralamat di {{ $penghuni->alamat_ktp ?? '' }} NIK: {{ $penghuni->nik ?? '' }}
                    <br>Yang selanjutnya disebut <b>PIHAK KEDUA</b>.
                </td>
            </tr>
            <tr>
                <td colspan="2" style="padding-top: 8px; text-align: justify;">
                    PIHAK PERTAMA dan PIHAK KEDUA, terlebih dahulu menerangkan bahwa:
                </td>
            </tr>
            <tr>
                <td class="num">1.</td>
                <td style="text-align: justify;">Bahwa PIHAK PERTAMA dan PIHAK KEDUA telah sepakat melakukan Perjanjian Sewa Rusunawa sebagaimana PIHAK KEDUA telah mengajukan pemohonan pendaftaran dengan Nomor Formulir</td>
            </tr>
            <tr>
                <td class="num">2.</td>
                @php
                    $tglSip = $kontrak->tanggal_sip ?? $kontrak->tanggal_masuk;
                @endphp
                <td style="text-align: justify;">Bahwa PIHAK PERTAMA telah menyetujui permohonan PIHAK KEDUA sebagaimana Surat Ijin Penghunian, Nomor {{ $kontrak->no_sip ?? '...' }} Tanggal {{ $tglSip ? ucwords(\App\Helpers\TerbilangHelper::convert($tglSip->format('d'))) : '...' }} Bulan {{ $tglSip ? $tglSip->translatedFormat('F') : '...' }} Tahun {{ $tglSip ? $tglSip->format('Y') : '...' }} dan Surat Perjanjian Sewa Menyewa Rusunawa Nomor {{ $kontrak->no_sps ?? '...' }} Tanggal {{ $tglSps ? ucwords(\App\Helpers\TerbilangHelper::convert($tglSps->format('d'))) : '...' }} Bulan {{ $tglSps ? $tglSps->translatedFormat('F') : '...' }} Tahun {{ $tglSps ? $tglSps->format('Y') : '...' }}</td>
            </tr>
            <tr>
                <td class="num">3.</td>
                <td style="text-align: justify;">PIHAK KEDUA sepakat dan tunduk kepada seluruh tata tertib serta ketentuan-ketentuan yang berkaitan dengan sistem dan prosedur penyewa Rumah Susun Sederhana Sewa {{ $namaRusun }}, serta seluruh ketentuan perundang-undangan yang berlaku dalam wilayah Republik Indonesia.</td>
            </tr>
            <tr>
                <td class="num">4.</td>
                <td style="text-align: justify;">PIHAK KEDUA tidak akan melakukan tuntutan dalam bentuk apapun terhadap PIHAK PERTAMA terhadap pengosongan seperti tercantum pada angka (4) diatas.</td>
            </tr>
            <tr>
                <td colspan="2" style="padding-top: 8px; text-align: justify;">
                    Maka PIHAK PERTAMA dan PIHAK KEDUA dengan pertimbangan-pertimbangan tersebut di atas sepakat untuk mengadakan perjanjian sewa menyewa dengan syarat-syarat dan ketentuan-ketentuan sebagai berikut:
                </td>
            </tr>

            <tr>
                <td colspan="2" class="pasal-title">KETENTUAN UMUM<br>Pasal 1</td>
            </tr>
            <tr>
                <td colspan="2" style="text-align: justify;">Kata-kata yang tercantum dalam perjanjian ini harus diartikan:</td>
            </tr>
            <tr>
                <td class="num">1.</td>
                <td style="text-align: justify;">Perjanjian sewa menyewa rumah susun adalah hubungan hukum antara PIHAK PERTAMA dengan PIHAK KEDUA mengenai kesepakatan sewa menyewa unit rumah susun dan memanfaatkan secara bersama untuk prasarana, sarana dan utilitas umum rumah susun.</td>
            </tr>
            <tr>
                <td class="num">2.</td>
                <td style="text-align: justify;">Biaya rumah susun sewa adalah sewa hunian dan iuran PDAM yang harus dibayar oleh penyewa/penghuni.</td>
            </tr>
            <tr>
                <td class="num">3.</td>
                <td style="text-align: justify;">Penyewa adalah penghuni/penyewa membayar biaya sewa dan telah mendapat persetujuan tertulis dari pengelola rumah susun untuk menghuni atau bertempat tinggal pada jangka waktu tertentu.</td>
            </tr>

            <tr>
                <td colspan="2" class="pasal-title">BIAYA SEWA<br>Pasal 2</td>
            </tr>
            <tr>
                <td class="num">1.</td>
                <td style="text-align: justify;">PIHAK KEDUA wajib membayar sewa rumah sebagaimana dimaksud pada Pasal 1 sebesar Rp. {{ number_format($kontrak->nominal_keringanan, 0, ',', '.') }} ({{ ucwords(\App\Helpers\TerbilangHelper::convert($kontrak->nominal_keringanan)) }} Rupiah ) per bulan yang harus dibayar pada bulan jatuh tempo, serta uang jaminan sewa sebesar 3 x (tiga kali) uang sewa pada saat perjanjian sewa menyewa ditandatangani oleh penyewa, dan iuran PDAM sebesar Rp. {{ number_format($kontrak->tarif_air, 0, ',', '.') }} per bulan.</td>
            </tr>
            <tr>
                <td class="num">2.</td>
                <td>Seluruh transaksi pembayaran yang dilakukan wajib memberikan bukti pembayaran yang sah atas pembayaran sewa unit rumah susun.</td>
            </tr>
            <tr>
                <td class="num">3.</td>
                <td>Apabila pembayaran sewa menyewa mengalami keterlambatan paling lambat 7 (tujuh) hari maka PIHAK KEDUA wajib membayar denda keterlambatan sebesar 10% (Sepuluh Persen) dari total sewa kepada PIHAK PERTAMA.</td>
            </tr>
            <tr>
                <td class="num">4.</td>
                <td>Bahwa PIHAK PERTAMA berhak untuk meninjau kembali biaya sewa pada saat perpanjangan sewa.</td>
            </tr>

            <tr>
                <td colspan="2" class="pasal-title">HAK DAN KEWAJIBAN<br>PIHAK KESATU<br>Pasal 3</td>
            </tr>
            <tr>
                <td colspan="2">Bahwa selama jangka waktu berlakunya perjanjian sewa menyewa ini berlangsung maka PIHAK PERTAMA berhak dan berkewajiban:</td>
            </tr>
            <tr><td class="num">1.</td><td>Wajib melakukan pemeriksaan dan perbaikan terhadap Hunian yang disewakan kepada PIHAK KEDUA.</td></tr>
            <tr><td class="num">2.</td><td>Wajib menjaga keamanan pada lingkungan rumah susun, menjaga kualitas lingkungan yang bersih dan sehat.</td></tr>
            <tr><td class="num">3.</td><td>Berhak menegur dan mengeluarkan secara paksa dengan memutuskan perjanjian secara sepihak terhadap PIHAK KEDUA yang membuat kegaduhan/kerusuhan dan/atau pengerusakan fasilitas rumah susun.</td></tr>
            <tr><td class="num">4.</td><td>Berhak untuk memberikan sanksi kepada PIHAK KEDUA terhadap pelanggaran tata tertib rumah susun dan</td></tr>
            <tr><td class="num">5.</td><td>Berhak melakukan pungutan uang sewa hunian serta denda.</td></tr>
            <tr><td class="num">6.</td><td>Berhak memindahkan hunian PIHAK KEDUA untuk sementara, apabila PIHAK PERTAMA membutuhkan ruang hunian PIHAK KEDUA, tanpa harus dengan persetujuan PIHAK KEDUA, selama dalam tarif sewa yang sama.</td></tr>
            <tr><td class="num">7.</td><td>Berhak mengganti kunci hunian PIHAK KEDUA, apabila PIHAK KEDUA melakukan pelanggaran terhadap perjanjian ini.</td></tr>
            <tr><td class="num">8.</td><td>Apabila PIHAK KEDUA meninggalkan hunian tanpa seijin PIHAK PERTAMA dengan meninggalkan Tunggakan SEWA HUNIAN dan iuran PDAM, maka PIHAK PERTAMA Berhak melaporkan ke Pihak Yang Berwajib.</td></tr>
   
            <tr>
                <td colspan="2" class="pasal-title">HAK DAN KEWAJIBAN<br>PIHAK KEDUA<br>Pasal 4</td>
            </tr>
            <tr>
                <td colspan="2">Bahwa selama jangka waktu berlakunya perjanjian sewa menyewa ini berlangsung, maka PIHAK KEDUA berhak dan berkewajiban:</td>
            </tr>
            <tr><td class="num">1.</td><td>Wajib membayar biaya sewa hunian dan air PDAM tepat waktu.</td></tr>
            <tr><td class="num">2.</td><td>Wajib membuang sampah pada tempat yang disediakan dengan membungkusnya ke dalam plastik secara rapih.</td></tr>
            <tr><td class="num">3.</td><td>Wajib melaporkan pada PIHAK PERTAMA melalui petugas keamanan rusun apabila kedatangan tamu yang akan menginap pada unit rumah susun dalam waktu paling lambat 1x 24 jam.</td></tr>
            <tr><td class="num">4.</td><td>Wajib menaati tata tertib Rusunawa.</td></tr>
            <tr><td class="num">5.</td><td>Wajib menjaga kebersihan, ketertiban dan kerapihan di depan hunian.</td></tr>
            <tr><td class="num">6.</td><td>Berhak menghuni rumah susun sewa untuk tempat tinggal bukan untuk usaha/dagang.</td></tr>
            <tr><td class="num">7.</td><td>Berhak untuk menggunakan fasilitas di lingkungan rumah susun seperti tempat parkir dan masjid.</td></tr>
            <tr><td class="num">8.</td><td>Berhak mengajukan pindah hunian ke hunian difabel di lantai dasar untuk sementara apabila benar-benar dibutuhkan.</td></tr>

            <tr>
                <td colspan="2" class="pasal-title">LARANGAN-LARANGAN<br>Pasal 5</td>
            </tr>
            <tr>
                <td colspan="2">Bahwa selama jangka waktu berlakunya perjanjian sewa menyewa ini berlangsung maka PIHAK KEDUA dilarang:</td>
            </tr>
            <tr><td class="num">1.</td><td>Menyewakan atau memindahkan sewa unit rumah susun kepada pihak lain dengan alasan apapun.</td></tr>
            <tr><td class="num">2.</td><td>Melakukan perubahan/perombakan unit rumah sewa dalam bentuk apapun.</td></tr>
            <tr><td class="num">3.</td><td>Menyimpan segala bahan kimia yang mudah terbakar atau bahan lainnya yang dapat menyebabkan bahaya terhadap rumah susun atau penghuni</td></tr>
            <tr><td class="num">4.</td><td>Melakukan perbuatan perjudian dalam bentuk apapun serta meminum minuman keras.</td></tr>
            <tr><td class="num">5.</td><td>Melakukan perbuatan maksiat yang melanggar kesusilaan umum dan</td></tr>
            <tr><td class="num">6.</td><td>Mengadakan pertemuan untuk berbuat kriminal, terorisme dan politik.</td></tr>
            <tr><td class="num">7.</td><td>Berbuat kegaduhan atau keributan yang menganggu ketentraman dan kenyamanan pengguna lain.</td></tr>
            <tr><td class="num">8.</td><td>Memelihara hewan peliharaan seperti anjing, kucing, binatang primata, binatang liar lainnya, kecuali burung dalam sangkar atau ikan dalam aquarium.</td></tr>
            <tr><td class="num">9.</td><td>Membawa, meletakkan, menaruh benda/barang yang beratnya melampaui batas yang telah ditentukan sehingga dapat membahayakan kontruksi bangunan rumah susun sewa.</td></tr>
            <tr><td class="num">10.</td><td>Membuang barang atau segala sesuatu secara sembarangan, termasuk membuang sesuatu dari tingkat atas ke bawah.</td></tr>
            <tr><td class="num">11.</td><td>Menganggu atau menghalang-halangi kegiatan pemeliharaan atau perbaikan rumah susun.</td></tr>
            <tr><td class="num">12.</td><td>Meletakkan barang atau sesuatu di ruang umum, tangga dan tempat fasilitas bersama lainnya</td></tr>
            <tr><td class="num">13.</td><td>Melakukan kegiatan transaksi atau memakai dan/atau penyalahgunaan narkotika dan obat-obatan keras yang dilarang oleh peraturan perundang-undangan.</td></tr>
            <tr><td class="num">14.</td><td>Dilarang menjemur pakaian diluar hunian.</td></tr>
            <tr><td class="num">15.</td><td>Dilarang memarkir kendaraan pada tempat yang bukan peruntukannya.</td></tr>
            <tr><td class="num">16.</td><td>Dilarang melakukan aktifitas yang mendatangkan kerumunan lebih dari 10 (sepuluh) orang tanpa ijin PIHAK PERTAMA.</td></tr>
            <tr><td class="num">17.</td><td>Dilarang membawa kendaraan roda 4 (empat) atau lebih ke dalam lingkungan rusunawa tanpa ijin PIHAK PERTAMA.</td></tr>
            
            <tr>
                <td colspan="2" class="pasal-title">PENGALIHAN<br>Pasal 6</td>
            </tr>
            <tr>
                <td colspan="2">
                    Perjanjian Sewa Menyewa antara PIHAK PERTAMA dan PIHAK KEDUA tidak dapat dialihkan baik untuk sebagian maupun keseluruhannya dengan alasan
                </td>
            </tr>

            <tr>
                <td colspan="2" class="pasal-title">KETENTUAN SANKSI<br>Pasal 7</td>
            </tr>
            <tr><td class="num">1.</td><td>Apabila PIHAK KEDUA dengan sengaja atau lalai melakukan pelanggaran sebagaimana dimaksud dalam Pasal 4 dan Pasal 5, Maka Perjanjian Sewa Menyewa batal demi hukum, dan PIHAK KEDUA bersedia memberikan penggantian kerugian kepada PIHAK PERTAMA sebesar jaminan sewa.</td></tr>
            <tr><td class="num">2.</td><td>Apabila dalam jangka waktu 1 (satu) bulan sejak penandatanganan perjanjian ini PIHAK KEDUA tidak atau belum menempati hunian rumah susun, maka PIHAK PERTAMA secara sepihak dapat membatalkan Perjanjian Sewa Menyewa, dan uang sewa berikut jaminan sewa yang telah disetorkan dan diterima PIHAK PERTAMA akan dikembalikan kepada PIHAK KEDUA setelah dipotong biaya administrasi sebesar 50% (lima puluh persen).</td></tr>
            <tr><td class="num">3.</td><td>PIHAK KEDUA meninggalkan unit rumah susun dengan seluruh barang-barang miliknya dalam jangka waktu 7 (tujuh) hari setelah memutuskan atau putus perjanjian sewa dan menyerahkan kunci beserta seluruh perlengkapan rumah kepada PIHAK PERTAMA.</td></tr>

            <tr>
                <td colspan="2" class="pasal-title">Pasal 8</td>
            </tr>
            <tr><td colspan="2">PIHAK KEDUA sepakat untuk mengesampingkan Pasal 1266 dan Pasal 1267 Kitab Undang-Undang Hukum Perdata dalam rangka pembatalan sepihak oleh PIHAK PERTAMA kepada PIHAK KEDUA dalam perjanjian sewa menyewa rumah susun sewa.</td></tr>

            <tr>
                <td colspan="2" class="pasal-title">PENYELESAIAN PERSELISIHAN<br>Pasal 9</td>
            </tr>
            <tr><td class="num">1.</td><td>Apabila terjadi perselisihan antara PIHAK PERTAMA dengan PIHAK KEDUA diselesaikan dengan cara musyawarah.</td></tr>
            <tr><td class="num">2.</td><td>Apabila musyawarah yang dilakukan tidak mencapai kesepakatan maka PIHAK PERTAMA dan PIHAK KEDUA sepakat untuk memilih penyelesaian perselisihan kepada Kantor Kepaniteraan Pengadilan Negeri Kota Tegal, dimana lokasi rumah susun didirikan.</td></tr>
            <tr><td colspan="2" style="padding-top: 12px;">Demikian Perjanjian sewa menyewa rumah susun ini dinyatakan sah dan mengikat para pihak yang dibuat rangkap 2 (dua) bermaterai cukup dan masing-masing mempunyai kekuatan hukum yang sama.</td></tr>
            <tr><td colspan="2" style="padding-top: 12px;">Perjanjian sewa menyewa rumah susun ini berlaku sejak para pihak menandatanganinya.</td></tr>
        </table>

        <div class="sps-signature-section">
            <div class="sps-signature-box-left">
                <p>PIHAK KEDUA</p>
                <br><br><br><br><br>
                <p class="sps-signature-nama">{{ $penghuni->nama }}</p>
            </div>
            <div class="sps-signature-box-right">
                <p>PIHAK PERTAMA</p>
                <p>KEPALA DINAS PERUMAHAN DAN</p>
                <p>KAWASAN PERMUKIMAN KOTA TEGAL</p>
                <br><br><br>
                <p class="sps-signature-nama">{{ $kepalaDinas->nama }}</p>
                @if($kepalaDinas->pangkat)
                    <p>{{ $kepalaDinas->pangkat }}</p>
                @endif
                @if($kepalaDinas->nip)
                    <p>NIP. {{ $kepalaDinas->nip }}</p>
                @endif
            </div>
            <div style="clear: both;"></div>
        </div>
    </div>
    
    <div class="page-break"></div>
    <div class="container">
        
        <p class="title" style="text-decoration: none; margin-bottom: 0; text-decoration: underline;">BERITA ACARA SERAH TERIMA UNIT HUNIAN</p>
        <p class="title" style="margin-top: 0; margin-bottom: 5px;">RUMAH SUSUN SEDERHANA SEWA</p>
        <p class="subtitle" style="text-decoration: none;">Nomor: 648/144.B</p>

        <div class="content" style="text-align: left;">
            <p>Berdasarkan pada Surat Perjanjian Sewa Menyewa Rusunawa Nomor {{ $kontrak->no_sps ?? '' }}, maka kepada tersebut di bawah ini:</p>
            <table class="data-table" style="margin-left: 0px; margin-top: 12px; margin-bottom: 12px;">
                <tr>
                    <td class="label">Nama</td>
                    <td class="separator">:&nbsp;</td>
                    <td class="value">{{ $penghuni->nama }}</td>
                </tr>
                <tr>
                    <td class="label">Tempat / tgl lahir</td>
                    <td class="separator">:&nbsp;</td>
                    <td class="value">
                        @php
                            $ttl = '';
                            if ($penghuni->tempat_lahir) { $ttl .= $penghuni->tempat_lahir; }
                            if ($penghuni->tanggal_lahir) {
                                if ($ttl) $ttl .= ', ';
                                $ttl .= \Carbon\Carbon::parse($penghuni->tanggal_lahir)->translatedFormat('d F Y');
                            }
                        @endphp
                        {{ $ttl ?: '' }}
                    </td>
                </tr>
                <tr>
                    <td class="label">Pekerjaan</td>
                    <td class="separator">:&nbsp;</td>
                    <td class="value">{{ $penghuni->pekerjaan ?? '' }}</td>
                </tr>
                <tr>
                    <td class="label">No. KTP</td>
                    <td class="separator">:&nbsp;</td>
                    <td class="value">{{ $penghuni->nik }}</td>
                </tr>
                <tr>
                    <td class="label">Alamat KTP</td>
                    <td class="separator">:&nbsp;</td>
                    <td class="value">{{ $penghuni->alamat_ktp ?? '' }}</td>
                </tr>
            </table>

            @php
                $blok = $unit->kode_unit ? substr($unit->kode_unit, 0, 1) : '-';
                $lantai = '-';
                $nomor = preg_replace("/[^0-9]/", "", $unit->kode_unit);

                if (in_array($blok, ['A', 'B', 'C']) && strlen($nomor) >= 3) {
                    $lantai = substr($nomor, 0, 1);
                } 
                elseif ($blok == 'D' && strlen($nomor) >= 3) {
                    $lantai = substr($nomor, 0, 1);
                }
            @endphp
            
            <p style="text-align: justify; margin-bottom: 0px;">
                Telah diserahkan 1 (satu) set kunci rumah dari 1 (satu) {{ $namaRusun }} Blok {{ $blok }} Lantai {{ $lantai }} Nomor {{ $nomor }} dalam keadaan baik, oleh karena itu yang bersangkutan berhak dan diizinkan Menempati / Menghuni Unit Hunian tersebut terhitung sejak tanggal surat ijin penghunian dikeluarkan dengan ketentuan sebagai berikut : 
            </p>
            <ol style="text-align: justify; padding-left: 20px; margin-top: 8px; font-size: 12pt; line-height: 1.4;">
                <li style="padding-left: 8px; margin-bottom: 3px;">memelihara, merawat, menjaga kebersihan unit hunian Rusunawa;</li>
                <li style="padding-left: 8px; margin-bottom: 3px;">tidak memindahkan hak sewa kepada pihak lain, dan tidak memanfaatkan hunian sebagai tempat usaha/gudang;</li>
                <li style="padding-left: 8px; margin-bottom: 3px;">tidak mengubah prasarana, sarana dan utilitas Rusunawa yang sudah ada;</li>
                <li style="padding-left: 8px; margin-bottom: 3px;">melaporkan adanya kerusakan pada prasarana, sarana dan utilitas di Rusunawa kepada pengelola;</li>
                <li style="padding-left: 8px; margin-bottom: 3px;">membayar ganti rugi untuk setiap kerusakan yang diakibatkan kelalaian penghuni;</li>
                <li style="padding-left: 8px; margin-bottom: 3px;">mengosongkan dan menyerahkan tempat hunian dalam keadaan baik kepada Pengelola pada saat perjanjian penghunian berakhir.</li>
                <li style="padding-left: 8px; margin-bottom: 3px;">mematuhi ketentuan sesuai yang tercantum dalam perjanjian sewa.</li>
            </ol>
            
            @php
                $pengelola = $staffList->get(1); 
            @endphp
            <div class="signature-section" style="margin-top: 20px;">
                <div class="signature-box left" style="width: 50%; float: left; text-align: center; line-height:1">
                    <p>PENYEWA,</p>
                    <br><br><br><br>
                    <p class="nama" style="text-transform: uppercase; font-weight:bold; text-decoration: underline;">{{ $penghuni->nama }}</p>
                </div>
                <div class="signature-box right" style="width: 50%; float: right; text-align: center;">
                    <p>PENGELOLA,</p>
                    <br><br><br>
                    <p class="nama" style="text-transform: uppercase; font-weight:bold; text-decoration:underline; line-height:1;">
                        {{ $pengelola ? $pengelola->nama : '(Nama Pengelola)' }}
                    </p>
                    @if($pengelola && $pengelola->nip)
                        <p class="nip" style="margin-top: none; text-decoration: none; line-height:1;">NIP. {{ $pengelola->nip }}</p>
                    @endif
                </div>
                <div style="clear: both;"></div>
            </div>
            
            <div class="bast-catatan">
                <p>
                    <strong>Catatan:</strong> Berita Acara Serah Terima ini agar disimpan dengan baik dan diperlihatkan apabila Pengelola Rusunawa atau Pihak yang berwajib mendatangi Unit Hunian Saudara.
                </p>
            </div>
        </div>
    </div>
    
</body>
</html>