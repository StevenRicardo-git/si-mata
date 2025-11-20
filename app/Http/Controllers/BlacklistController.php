<?php
namespace App\Http\Controllers;

use App\Models\Blacklist;
use App\Models\Penghuni;
use App\Models\Kontrak;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Services\AuditBatchService;
use Illuminate\Support\Facades\Log;

class BlacklistController extends Controller
{
    public function index(Request $request)
    {
        $query = Blacklist::query();
        
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama', 'LIKE', "%{$search}%")
                  ->orWhere('nik', 'LIKE', "%{$search}%");
            });
        }
        
        $query->orderBy('status', 'asc')
              ->orderBy('tanggal_blacklist', 'desc');
        
        $perPage = $request->get('per_page', 25);
        $blacklist = $query->paginate($perPage);
        
        return view('blacklist.index', compact('blacklist'));
    }

    public function create()
    {
        return view('blacklist.create');
    }

    public function store(Request $request)
    {
        $existingBlacklist = Blacklist::where('nik', $request->nik)
            ->where('status', 'blacklist')
            ->first();
        
        if ($existingBlacklist) {
            return redirect()->back()
                ->withInput()
                ->with('error', "NIK {$request->nik} sudah ada di daftar hitam!<br>
                    <strong>Nama:</strong> {$existingBlacklist->nama}<br>
                    <strong>Alasan:</strong> {$existingBlacklist->alasan_blacklist}<br>
                    <strong>Tanggal:</strong> " . $existingBlacklist->tanggal_blacklist->format('d M Y'));
        }

        $validatedData = $request->validate([
            'nama' => 'required|string|max:255',
            'nik' => 'required|string|size:16',
            'hubungan' => 'nullable|string|max:255',
            'alasan_blacklist' => 'required|string',
        ], [
            'nik.required' => 'NIK wajib diisi.',
            'nik.size' => 'NIK harus 16 digit.',
            'nama.required' => 'Nama wajib diisi.',
            'alasan_blacklist.required' => 'Alasan blacklist wajib diisi.',
        ]);

        try {
            DB::beginTransaction();

            $penghuni = Penghuni::where('nik', $validatedData['nik'])->first();
            AuditBatchService::start("Menambah ke blacklist: {$validatedData['nama']}");

            if (!$penghuni) {
                $penghuni = Penghuni::create([
                    'nik' => $validatedData['nik'],
                    'nama' => strtoupper($validatedData['nama']),
                    'tempat_lahir' => null,
                    'tanggal_lahir' => null,
                    'jenis_kelamin' => null,
                    'pekerjaan' => 'Tidak diketahui',
                    'no_hp' => null,
                    'alamat_ktp' => 'Ditambahkan dari Blacklist Manual',
                    'status' => 'tidak_aktif',
                ]);
            }

            $kontrakAktif = Kontrak::where('penghuni_id', $penghuni->id)
                ->where('status', 'aktif')
                ->get();

            foreach ($kontrakAktif as $kontrak) {
                $kontrak->status = 'keluar';
                $kontrak->tanggal_keluar_aktual = now();
                $kontrak->alasan_keluar = 'Di-blacklist: ' . $validatedData['alasan_blacklist'];
                $kontrak->save();

                if ($kontrak->unit) {
                    $kontrak->unit->status = 'tersedia';
                    $kontrak->unit->save();
                }
            }

            $penghuni->status = 'tidak_aktif';
            $penghuni->save();

            $blacklist = Blacklist::updateOrCreate(
                ['nik' => $validatedData['nik']],
                [
                    'nama' => strtoupper($validatedData['nama']),
                    'hubungan' => $validatedData['hubungan'] ?? 'Manual',
                    'alasan_blacklist' => $validatedData['alasan_blacklist'],
                    'tanggal_blacklist' => now(),
                    'status' => 'blacklist',
                    'alasan_aktivasi' => null,
                    'tanggal_aktivasi' => null,
                ]
            );

            AuditBatchService::end();

            DB::commit();

            return redirect()->route('blacklist.index')
                ->with('success', "Data {$blacklist->nama} (NIK: {$blacklist->nik}) telah berhasil ditambahkan ke daftar hitam. " . 
                    ($kontrakAktif->count() > 0 ? "{$kontrakAktif->count()} kontrak aktif telah diakhiri." : ""));

        } catch (\Exception $e) {
            AuditBatchService::end();
            DB::rollBack();
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $request->validate([
                'alasan_aktivasi' => 'required|string|min:10',
            ], [
                'alasan_aktivasi.required' => 'Alasan aktivasi wajib diisi',
                'alasan_aktivasi.min' => 'Alasan aktivasi minimal 10 karakter'
            ]);
            
            $blacklist = Blacklist::findOrFail($id);
            
            $blacklist->status = 'aktif';
            $blacklist->alasan_aktivasi = $request->alasan_aktivasi;
            $blacklist->tanggal_aktivasi = now();
            $blacklist->save();
            
            $penghuni = Penghuni::where('nik', $blacklist->nik)->first();
            if ($penghuni) {
                $penghuni->status = 'tidak_aktif';
                $penghuni->save();
            }
            
            $namaUppercased = strtoupper($blacklist->nama);

            return redirect()->back()
                ->with('success', "Data NIK {$blacklist->nik} (Nama: {$namaUppercased}) berhasil diaktifkan kembali. Penghuni dapat membuat kontrak baru.");
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Validasi gagal: ' . collect($e->errors())->flatten()->first());
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    
    public function checkNik(Request $request)
    {
        try {
            $nik = $request->query('nik');
            
            if (!$nik || strlen($nik) !== 16) {
                return response()->json([
                    'exists' => false,
                    'is_blacklisted' => false,
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
                    'blacklist_info' => [
                        'nama' => strtoupper($blacklistEntry->nama),
                        'alasan' => $blacklistEntry->alasan_blacklist,
                        'tanggal' => $blacklistEntry->tanggal_blacklist->format('d M Y')
                    ],
                    'message' => 'NIK sudah ada di blacklist'
                ], 200);
            }
            
            $penghuni = Penghuni::where('nik', $nik)->first();
            
            if ($penghuni) {
                return response()->json([
                    'exists' => true,
                    'is_blacklisted' => false,
                    'nama' => strtoupper($penghuni->nama)
                ], 200);
            }
            
            return response()->json([
                'exists' => false,
                'is_blacklisted' => false,
                'message' => 'NIK tidak ditemukan'
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'exists' => false,
                'is_blacklisted' => false,
                'error' => 'Terjadi kesalahan sistem'
            ], 500);
        }
    }
}