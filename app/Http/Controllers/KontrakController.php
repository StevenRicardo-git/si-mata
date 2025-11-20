<?php

namespace App\Http\Controllers;

use App\Models\Kontrak;
use App\Models\Penghuni;
use App\Models\Unit;
use App\Models\Disperkim;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\AuditBatchService;
use Illuminate\Support\Facades\DB;
use App\Helpers\TerbilangHelper;
use Carbon\Carbon;
use Illuminate\Support\Str;

class KontrakController extends Controller
{
    public function store(Request $request)
    {   
        $request->merge([
            'nominal_keringanan' => $request->nominal_keringanan ? preg_replace('/[^0-9]/', '', $request->nominal_keringanan) : 0,
            'nilai_jaminan' => $request->nilai_jaminan ? preg_replace('/[^0-9]/', '', $request->nilai_jaminan) : 0,
            'tarif_air' => $request->tarif_air ? preg_replace('/[^0-9]/', '', $request->tarif_air) : 0,
        ]);

        try {
            $validatedData = $request->validate([
                'penghuni_id' => 'required|exists:penghuni,id',
                'unit_kode' => 'required|string',
                'tanggal_masuk' => 'required|date',
                'tanggal_keluar' => 'nullable|date',
                'keringanan' => 'required|in:dapat,tidak,normal',
                'nominal_keringanan' => 'required|numeric|min:0',
                'tarif_air' => 'required|numeric|min:0',
                'tanggal_sps' => 'nullable|date',
                'no_sps' => 'nullable|string|max:255',
                'nilai_jaminan' => 'nullable|numeric|min:0',
                'no_sip' => 'nullable|string|max:255',
                'tanggal_sip' => 'nullable|date',
                'alasan_keluar' => 'nullable|string',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Validasi gagal: ' . collect($e->errors())->flatten()->first());
        }
        
        $existingActiveContract = Kontrak::where('penghuni_id', $validatedData['penghuni_id'])
            ->where('status', 'aktif')
            ->first();

        if ($existingActiveContract) {
            return back()
                ->withInput()
                ->with('error', 'Penghuni ini sudah memiliki kontrak aktif di unit ' . 
                    ($existingActiveContract->unit->kode_unit ?? '-') . 
                    '. Silakan akhiri kontrak yang ada terlebih dahulu.');
        }
        
        $penghuni = Penghuni::find($validatedData['penghuni_id']);
        
        $unit = Unit::where('kode_unit', $validatedData['unit_kode'])->first();
        
        if (!$unit) {
            try {
                $unit = Unit::create([
                    'kode_unit' => $validatedData['unit_kode'],
                    'tipe' => $this->getUnitType($validatedData['unit_kode']),
                    'status' => 'tersedia',
                    'harga_sewa' => 0
                ]);
            } catch (\Exception $e) {
                return back()
                    ->withInput()
                    ->with('error', 'Gagal membuat unit baru: ' . $e->getMessage());
            }
        }
        
        $existingKontrak = Kontrak::where('unit_id', $unit->id)
            ->where('status', 'aktif')
            ->first();
            
        if ($existingKontrak) {
            $occupantName = $existingKontrak->penghuni->nama ?? 'Unknown';
            return back()
                ->withErrors(['unit_kode' => 'Unit ini sudah terisi oleh ' . $occupantName . '.'])
                ->withInput()
                ->with('error', 'Unit sudah terisi oleh penghuni lain!');
        }

        $dataToStore = [
            'penghuni_id' => $validatedData['penghuni_id'],
            'unit_id' => $unit->id,
            'tanggal_masuk' => $validatedData['tanggal_masuk'],
            'tanggal_keluar' => $validatedData['tanggal_keluar'] ?? null,
            'keringanan' => $validatedData['keringanan'],
            'nominal_keringanan' => $validatedData['nominal_keringanan'],
            'tarif_air' => $validatedData['tarif_air'],
            'no_sps' => $validatedData['no_sps'] ?? null,
            'tanggal_sps' => $validatedData['tanggal_sps'] ?? null,
            'no_sip' => $validatedData['no_sip'] ?? null,
            'tanggal_sip' => $validatedData['tanggal_sip'] ?? null,
            'nilai_jaminan' => $validatedData['nilai_jaminan'] ?? 0,
            'alasan_keluar' => $validatedData['alasan_keluar'] ?? null,
            'status' => 'aktif'
        ];
        
        try {
            $kontrak = Kontrak::create($dataToStore);

            $penghuni->status = 'aktif';
            $penghuni->save();
            $unit->status = 'terisi';
            $unit->save();

            return redirect()
                ->route('penghuni.show', $penghuni->id)
                ->with('success', 'Kontrak baru berhasil dibuat dan status penghuni telah diaktifkan!');
                
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan kontrak: ' . $e->getMessage());
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
    
    public function terminate(Request $request, Kontrak $kontrak)
    {   
        $request->merge([
            'tunggakan' => $request->tunggakan ? preg_replace('/[^0-9]/', '', $request->tunggakan) : null,
        ]);

        $rules = [
            'tanggal_keluar' => [
                'required',
                'date',
                'after_or_equal:' . $kontrak->tanggal_masuk->format('Y-m-d'),
            ],
            'tunggakan' => 'nullable|numeric',
            'alasan_keluar' => 'nullable|string',
        ];

        if ($kontrak->tanggal_keluar) {
            $rules['tanggal_keluar'][] = 'before_or_equal:' . $kontrak->tanggal_keluar->format('Y-m-d');
        }

        $validatedData = $request->validate($rules);

        try {
            DB::beginTransaction();
            
            $penghuniNama = $kontrak->penghuni->nama;
            $unitKode = $kontrak->unit->kode_unit ?? 'N/A';
            AuditBatchService::start("Mengakhiri Kontrak: {$penghuniNama} telah keluar dari Unit {$unitKode}");

            $kontrak->tanggal_keluar_aktual = $validatedData['tanggal_keluar'];
            $kontrak->tunggakan = $validatedData['tunggakan'] ?? null;
            $kontrak->alasan_keluar = $validatedData['alasan_keluar'] ?? null;
            $kontrak->status = 'keluar';
            $kontrak->save();

            $penghuni = $kontrak->penghuni;
            $penghuni->status = 'tidak_aktif';
            $penghuni->save();
            
            $unit = $kontrak->unit;
            if($unit) {
                $unit->status = 'tersedia';
                $unit->save();
            }

            AuditBatchService::end();
            
            DB::commit();

            return redirect()->route('penghuni.show', $penghuni->id)
                ->with('success', 'Kontrak berhasil diakhiri dan status penghuni telah diperbarui.');
                
        } catch (\Exception $e) {
            AuditBatchService::end();
            DB::rollBack();
            
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function cetakSip($id)
    {
        $kontrak = Kontrak::with('penghuni.keluarga', 'unit')->findOrFail($id);

        if (empty($kontrak->no_sip)) {
            return redirect()->back()->with('error', 'Kontrak ini tidak memiliki No. SIP.'); 
        }

        $penghuni = $kontrak->penghuni;
        $unit = $kontrak->unit;

        $kepalaDinas = Disperkim::getKepalaDinasAktif();
        if (!$kepalaDinas) {
            $link = route('disperkim.index');
            $message = 'Data Kepala Dinas Aktif tidak ditemukan! Silakan periksa menu <a href="' . $link . '" style="text-decoration: underline; font-weight: bold;">Disperkim</a>.';
            
            return redirect()->back()->with('error', $message);
        }
        $staffList = Disperkim::getStaffAktif();

        $namaRusun = 'Rusunawa'; 
        if ($unit->tipe == 'Rumah Susun Sederhana') {
            $namaRusun = 'Rusunawa Kraton';
        } elseif ($unit->tipe == 'MBR Tegalsari') {
            $namaRusun = 'Rusunawa MBR Tegalsari';
        }
        
        $tanggalTtd = $kontrak->tanggal_sip ?? $kontrak->tanggal_masuk;
        $tanggal_cetak = $tanggalTtd ? Carbon::parse($tanggalTtd)->translatedFormat('d F Y') : Carbon::now()->translatedFormat('d F Y');

        $data = [
            'kontrak' => $kontrak,
            'penghuni' => $penghuni,
            'unit' => $unit,
            'tanggal_cetak' => $tanggal_cetak,
            'namaRusun' => $namaRusun,
            'kepalaDinas' => $kepalaDinas,
            'staffList' => $staffList,
        ];

        $pdf = Pdf::loadView('surat.sip.sip_pdf', $data); 
        $pdf->setPaper('folio', 'portrait');
        $fileName = 'SIP' . $penghuni->nama . ' ' . $unit->kode_unit . '.pdf';
        return $pdf->stream($fileName);
    }

    public function editBaKeluar($id)
    {
        $kontrak = Kontrak::with(['penghuni', 'unit'])->findOrFail($id);
        
        if (!$kontrak->tanggal_keluar_aktual && !$kontrak->tanggal_keluar) {
            return redirect()->route('penghuni.show', $kontrak->penghuni_id)
                ->with('error', 'Kontrak ini belum diakhiri!');
        }

        $kepalaDinas = Disperkim::getKepalaDinasAktif();
        $staff = Disperkim::getStaffAktif();

        $tanggalKeluarAktual = $kontrak->tanggal_keluar_aktual ?? $kontrak->tanggal_keluar;

        $formData = [
            'penghuni_nama' => $kontrak->penghuni->nama,
            'unit_kode' => $kontrak->unit->kode_unit,
            'nomor_ba' => old('nomor_ba', ''),
            'tanggal_pemutusan_perjanjian_sewa' => old('tanggal_pemutusan_perjanjian_sewa', $this->formatTanggalIndonesia($tanggalKeluarAktual)),
            'tanggal_ba' => old('tanggal_ba', $this->formatTanggalIndonesia($tanggalKeluarAktual)),
            'alasan_keluar' => old('alasan_keluar', $kontrak->alasan_keluar ?? 'mengundurkan diri dari hunian Rusunawa'),
            'tunggakan_sewa' => old('tunggakan_sewa', 0),
            'periode_tunggakan_sewa' => old('periode_tunggakan_sewa', ''),
            'tunggakan_denda' => old('tunggakan_denda', 0),
            'periode_tunggakan_denda' => old('periode_tunggakan_denda', ''),
            'tunggakan_air' => old('tunggakan_air', 0),
            'tunggakan_listrik' => old('tunggakan_listrik', 0),
            'tanggal_pelunasan' => old('tanggal_pelunasan', ''),
        ];

        return view('surat.ba.edit_ba_keluar', compact('kontrak', 'formData', 'kepalaDinas', 'staff'));
    }

    public function generatePdfBaKeluar(Request $request, $id)
    {
        $request->merge([
            'tunggakan_sewa' => $request->tunggakan_sewa ? preg_replace('/[^0-9]/', '', $request->tunggakan_sewa) : 0,
            'tunggakan_denda' => $request->tunggakan_denda ? preg_replace('/[^0-9]/', '', $request->tunggakan_denda) : 0,
            'tunggakan_air' => $request->tunggakan_air ? preg_replace('/[^0-9]/', '', $request->tunggakan_air) : 0,
            'tunggakan_listrik' => $request->tunggakan_listrik ? preg_replace('/[^0-9]/', '', $request->tunggakan_listrik) : 0,
        ]);

        $validated = $request->validate([
            'nomor_ba' => 'required|string|max:255',
            'tanggal_pemutusan_perjanjian_sewa' => 'required|string|max:255',
            'tanggal_ba' => 'required|string|max:255',
            'alasan_keluar' => 'required|string',
            'tunggakan_sewa' => 'nullable|numeric|min:0',
            'periode_tunggakan_sewa' => 'nullable|string|max:255',
            'tunggakan_denda' => 'nullable|numeric|min:0',
            'periode_tunggakan_denda' => 'nullable|string|max:255',
            'tunggakan_air' => 'nullable|numeric|min:0',
            'tunggakan_listrik' => 'nullable|numeric|min:0',
            'tanggal_pelunasan' => 'nullable|string|max:255',
        ]);

        $kontrak = Kontrak::with(['penghuni', 'unit'])->findOrFail($id);
        
        if (!$kontrak->tanggal_keluar_aktual && !$kontrak->tanggal_keluar) {
            return back()->with('error', 'Kontrak ini belum diakhiri!');
        }

        $kepalaDinas = Disperkim::getKepalaDinasAktif();
        $staff = Disperkim::getStaffAktif();

        if (!$kepalaDinas) {
            return back()->with('error', 'Data Kepala Dinas yang aktif tidak ditemukan! Silakan tambahkan di menu Disperkim.');
        }

        if ($staff->count() < 1) {
            return back()->with('error', 'Data Staff/Saksi minimal 1 orang! Silakan tambahkan di menu Disperkim.');
        }

        $penghuni = $kontrak->penghuni;
        $unit = $kontrak->unit;

        $form = $validated;
        $tanggalKeluarAktual = $kontrak->tanggal_keluar_aktual ?? $kontrak->tanggal_keluar;
        $carbonDate = \Carbon\Carbon::parse($tanggalKeluarAktual);
        $hari = $carbonDate->translatedFormat('l');
        $tanggalTerbilang = \App\Helpers\TerbilangHelper::convert($carbonDate->day);
        $bulan = $carbonDate->translatedFormat('F');
        $tahunTerbilang = \App\Helpers\TerbilangHelper::convert($carbonDate->year);
        $tanggalSpelledOut = $hari . ' Tanggal ' . ucwords($tanggalTerbilang) . ' Bulan ' . $bulan . ' Tahun ' . ucwords($tahunTerbilang);
        $tanggalNumeric = $carbonDate->format('d - m - Y');
        $form['tanggal_pemutusan_spelled_out'] = $tanggalSpelledOut;
        $form['tanggal_pemutusan_numeric'] = $tanggalNumeric;
        $nilai_jaminan = $kontrak->nilai_jaminan ?? 0;
        
        $jumlah_tunggakan = ($validated['tunggakan_sewa'] ?? 0) 
            + ($validated['tunggakan_denda'] ?? 0)
            + ($validated['tunggakan_air'] ?? 0)
            + ($validated['tunggakan_listrik'] ?? 0);
        
        $sisa_tunggakan = max(0, $jumlah_tunggakan - $nilai_jaminan);
        $jaminan_terbilang = \App\Helpers\TerbilangHelper::convert($nilai_jaminan);

        $kepala_dinas_nama = $kepalaDinas->nama;
        $kepala_dinas_nip = $kepalaDinas->nip;
        $kepala_dinas_pangkat = $kepalaDinas->pangkat;
        $kepala_dinas_jabatan = $kepalaDinas->jabatan;

        $staff_list = $staff;

        $pdf = PDF::loadView('surat.ba.ba_keluar_pdf', compact(
            'kontrak',
            'penghuni',
            'unit',
            'form',
            'nilai_jaminan',
            'jaminan_terbilang',
            'jumlah_tunggakan',
            'sisa_tunggakan',
            'kepala_dinas_nama',
            'kepala_dinas_nip',
            'kepala_dinas_pangkat',
            'kepala_dinas_jabatan',
            'staff_list'
        ));

        $pdf->setPaper('folio', 'portrait');

        $fileName = 'BA Keluar ' . $penghuni->nama . ' ' . $unit->kode_unit . ' ' . date('Ymd_His') . '.pdf';
        
        return $pdf->stream($fileName);
    }

    private function formatTanggalIndonesia($date)
    {
        if (!$date) return '';
        
        $hari = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin', 
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu'
        ];
        
        $bulan = [
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];
        
        $namaHari = $hari[date('l', strtotime($date))];
        $tanggal = date('d', strtotime($date));
        $bulanAngka = date('n', strtotime($date));
        $tahun = date('Y', strtotime($date));
        
        return $namaHari . ', ' . $tanggal . ' ' . $bulan[$bulanAngka] . ' ' . $tahun;
    }
}