<?php

namespace App\Http\Controllers;

use App\Models\Disperkim;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use OwenIt\Auditing\Facades\Auditor;

class DisperkimController extends Controller
{
    public function index()
    {
        $kepalaDinas = Disperkim::kepalaDinas()->ordered()->get();
        $staff = Disperkim::staff()->ordered()->get();
        
        return view('disperkim.index', compact('kepalaDinas', 'staff'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tipe' => 'required|in:kepala_dinas,staff',
            'nama' => 'required|string|max:255',
            'jabatan' => 'required|string|max:255',
            'nip' => 'required|string|max:18',
            'pangkat' => 'nullable|string|max:100',
        ]);

        if ($validated['tipe'] === 'kepala_dinas') {
            $kepalaDinasAktif = Disperkim::kepalaDinas()->aktif()->first();
            
            if ($kepalaDinasAktif) {
                return redirect()->route('disperkim.index')
                    ->with('error', 'Tidak dapat menambahkan Kepala Dinas baru. Sudah ada Kepala Dinas aktif (' . $kepalaDinasAktif->nama . '). Silakan nonaktifkan atau hapus Kepala Dinas yang ada terlebih dahulu.');
            }
        }

        $maxUrutan = Disperkim::where('tipe', $validated['tipe'])->max('urutan') ?? 0;
        
        $validated['urutan'] = $maxUrutan + 1;
        $validated['aktif'] = true;

        Disperkim::create($validated);

        return redirect()->route('disperkim.index')
            ->with('success', 'Data berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {

        $disperkim = Disperkim::findOrFail($id);

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'jabatan' => 'required|string|max:255',
            'nip' => 'required|string|max:50',
            'pangkat' => 'nullable|string|max:100',
        ]);

        $disperkim->update($validated);

        return redirect()->route('disperkim.index')
            ->with('success', 'Data berhasil diperbarui!');
    }

    public function toggleStatus($id)
    {
        $disperkim = Disperkim::findOrFail($id);
        $disperkim->aktif = !$disperkim->aktif;
        $disperkim->save();

        $status = $disperkim->aktif ? 'diaktifkan' : 'dinonaktifkan';
        
        return redirect()->route('disperkim.index')
            ->with('success', "Data berhasil {$status}!");
    }

    public function destroy($id)
    {
        $disperkim = Disperkim::findOrFail($id);
        $disperkim->delete();

        return redirect()->route('disperkim.index')
            ->with('success', 'Data berhasil dihapus!');
    }

    public function updateUrutan(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:disperkim,id',
            'items.*.urutan' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            foreach ($validated['items'] as $item) {
                Disperkim::where('id', $item['id'])->update(['urutan' => $item['urutan']]);
            }
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Urutan berhasil diperbarui!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui urutan: ' . $e->getMessage()
            ], 500);
        }
    }
}