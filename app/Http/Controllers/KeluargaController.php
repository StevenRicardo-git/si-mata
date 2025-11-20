<?php

namespace App\Http\Controllers;

use App\Models\Keluarga;
use App\Models\Blacklist;
use App\Models\Penghuni;
use App\Models\Kontrak;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\AuditBatchService;
use Illuminate\Support\Str;
use OwenIt\Auditing\Facades\Auditor;

class KeluargaController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'penghuni_id' => 'required|exists:penghuni,id',
            'nama' => 'required|string|max:255',
            'nik' => 'nullable|string|size:16',
            'umur' => 'nullable|integer|min:0|max:150',
            'jenis_kelamin' => 'required|in:laki-laki,perempuan',
            'hubungan' => 'required|in:istri,suami,anak,lainnya',
            'catatan' => 'nullable|string',
        ]);
        
        $validated['nama'] = strtoupper($validated['nama']);
        
        if (isset($validated['catatan']) && $validated['catatan']) {
            $validated['catatan'] = strtoupper($validated['catatan']);
        }

        try {
            DB::beginTransaction();
            
            $penghuni = Penghuni::findOrFail($validated['penghuni_id']);
            AuditBatchService::start("Menambah anggota keluarga {$validated['nama']} untuk {$penghuni->nama}");

            Keluarga::create($validated);

            AuditBatchService::end();
            DB::commit();

            return redirect()->back()->with('success', 'Anggota keluarga berhasil ditambahkan!');
            
        } catch (\Exception $e) {
            AuditBatchService::end();
            DB::rollBack();
            
            return redirect()->back()->with('error', 'Gagal menambahkan anggota keluarga: ' . $e->getMessage());
        }
    }

    public function update(Request $request, Keluarga $keluarga)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'nik' => 'nullable|string|size:16',
            'umur' => 'nullable|integer|min:0|max:150',
            'jenis_kelamin' => 'required|in:laki-laki,perempuan',
            'hubungan' => 'required|in:istri,suami,anak,lainnya',
            'catatan' => 'nullable|string',
        ]);
        
        $validated['nama'] = strtoupper($validated['nama']);
        
        if (isset($validated['catatan']) && $validated['catatan']) {
            $validated['catatan'] = strtoupper($validated['catatan']);
        }

        try {
            DB::beginTransaction();

            $penghuniNama = $keluarga->penghuni->nama;
            
            AuditBatchService::start("Memperbarui data keluarga {$validated['nama']} dari {$penghuniNama}");

            $keluarga->update($validated);

            AuditBatchService::end();
            DB::commit();

            return redirect()->back()->with('success', 'Data anggota keluarga berhasil diperbarui!');
            
        } catch (\Exception $e) {
            AuditBatchService::end();
            DB::rollBack();
            
            return redirect()->back()->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    public function destroy(Keluarga $keluarga)
    {
        try {
            DB::beginTransaction();

            $namaKeluarga = $keluarga->nama;
            $penghuniNama = $keluarga->penghuni->nama;
            
            AuditBatchService::start("Menghapus anggota keluarga {$namaKeluarga} dari {$penghuniNama}");

            $keluarga->delete();

            AuditBatchService::end();
            DB::commit();

            return redirect()->back()->with('success', 'Data anggota keluarga berhasil dihapus!');
            
        } catch (\Exception $e) {
            AuditBatchService::end();
            DB::rollBack();
            
            return redirect()->back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    public function blacklist(Request $request, string $id)
    {
        $request->validate([
            'alasan_blacklist' => 'required',
        ]);

        try {
            DB::beginTransaction();

            $keluarga = Keluarga::findOrFail($id);
            
            AuditBatchService::start("Blacklist anggota keluarga: {$keluarga->nama}");

            if (!$keluarga->nik) {
                AuditBatchService::end();
                return redirect()->back()->with('error', 'Anggota keluarga ini tidak memiliki NIK, tidak dapat di-blacklist.');
            }

            $existingBlacklist = Blacklist::where('nik', $keluarga->nik)
                ->where('status', 'blacklist')
                ->first();

            if ($existingBlacklist) {
                AuditBatchService::end();
                return redirect()->back()->with('error', 'NIK ini sudah ada di daftar hitam!');
            }

            $penghuniUtama = Penghuni::where('nik', $keluarga->nik)->first();
            
            if ($penghuniUtama) {
                $kontrakAktif = Kontrak::where('penghuni_id', $penghuniUtama->id)
                    ->where('status', 'aktif')
                    ->get();

                foreach ($kontrakAktif as $kontrak) {
                    $kontrak->status = 'keluar';
                    $kontrak->tanggal_keluar_aktual = now();
                    $kontrak->alasan_keluar = 'Anggota keluarga di-blacklist: ' . $request->alasan_blacklist;
                    $kontrak->save();

                    if ($kontrak->unit) {
                        $kontrak->unit->status = 'tersedia';
                        $kontrak->unit->save();
                    }
                }

                $penghuniUtama->status = 'tidak_aktif';
                $penghuniUtama->save();
            }

            Blacklist::create([
                'nik' => $keluarga->nik,
                'nama' => $keluarga->nama,
                'hubungan' => ucfirst($keluarga->hubungan) . ' dari ' . $keluarga->penghuni->nama,
                'alasan_blacklist' => $request->alasan_blacklist,
                'tanggal_blacklist' => now(),
                'status' => 'blacklist',
            ]);

            AuditBatchService::end();
            DB::commit();

            return redirect()->back()->with('success', 'Anggota keluarga berhasil dimasukkan ke daftar hitam!');
            
        } catch (\Exception $e) {
            AuditBatchService::end();
            DB::rollBack();
            
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}