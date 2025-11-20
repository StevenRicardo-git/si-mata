<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PenghuniController;
use App\Http\Controllers\TagihanController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\KontrakController;
use App\Http\Controllers\KeluargaController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\BlacklistController;
use App\Http\Controllers\DisperkimController;
use App\Helpers\TerbilangHelper;
use Illuminate\Support\Facades\Artisan;

Route::get('/jalankan-migrasi', function () {
    try {
        Artisan::call('migrate', ['--force' => true]);
        return '<h1>Sukses! 🎉</h1><p>Database berhasil di-migrate.</p><pre>' . Artisan::output() . '</pre>';
    } catch (\Exception $e) {
        return '<h1>Gagal 😭</h1><p>' . $e->getMessage() . '</p>';
    }
});

Route::redirect('/', '/login');

Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'authenticate'])->name('login.authenticate');

Route::post('/create-user', [LoginController::class, 'createUser'])->name('user.create');
Route::get('/blacklist/check-nik', [BlacklistController::class, 'checkNik'])->name('blacklist.checkNik');
Route::get('/penghuni/check-nik', [PenghuniController::class, 'checkNik'])->name('penghuni.checkNik');

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    Route::get('/penghuni/import', [PenghuniController::class, 'import'])->name('penghuni.import');
    Route::post('/penghuni/import', [PenghuniController::class, 'importStore'])->name('penghuni.import.store');
    Route::get('/tambah-penghuni', [PenghuniController::class, 'create'])->name('tambah.penghuni');
    Route::get('/penghuni/{id}/cetak-surat-pernyataan', [PenghuniController::class, 'cetakSuratPernyataan'])->name('penghuni.cetakSurat');
    Route::post('/penghuni/store-with-kontrak', [PenghuniController::class, 'storeWithKontrak'])->name('penghuni.storeWithKontrak');
    Route::get('/penghuni/export-pdf', [PenghuniController::class, 'exportPdf'])->name('penghuni.exportPdf');
    Route::get('/penghuni/export-excel', [PenghuniController::class, 'exportExcel'])->name('penghuni.exportExcel');
    Route::get('/units/available', [PenghuniController::class, 'getAvailableUnits']);
    Route::resource('penghuni', PenghuniController::class);

    Route::get('/blacklist/create', [BlacklistController::class, 'create'])->name('blacklist.create');
    Route::post('/blacklist', [BlacklistController::class, 'store'])->name('blacklist.store');
    Route::get('/blacklist', [BlacklistController::class, 'index'])->name('blacklist.index');
    Route::put('/blacklist/{id}', [BlacklistController::class, 'update'])->name('blacklist.update');
    Route::post('/penghuni/{id}/blacklist', [PenghuniController::class, 'blacklist'])->name('penghuni.blacklist');
    Route::post('/keluarga/{id}/blacklist', [KeluargaController::class, 'blacklist'])->name('keluarga.blacklist');

    Route::get('/disperkim', [App\Http\Controllers\DisperkimController::class, 'index'])->name('disperkim.index');
    Route::post('/disperkim', [App\Http\Controllers\DisperkimController::class, 'store'])->name('disperkim.store');
    Route::put('/disperkim/{id}', [App\Http\Controllers\DisperkimController::class, 'update'])->name('disperkim.update');
    Route::post('/disperkim/{id}/toggle', [App\Http\Controllers\DisperkimController::class, 'toggleStatus'])->name('disperkim.toggle');
    Route::delete('/disperkim/{id}', [App\Http\Controllers\DisperkimController::class, 'destroy'])->name('disperkim.destroy');
    Route::post('/disperkim/update-urutan', [App\Http\Controllers\DisperkimController::class, 'updateUrutan'])->name('disperkim.updateUrutan');
    
    Route::get('/units/available', [UnitController::class, 'getAvailableUnits'])->name('units.available');
    Route::get('/penghuni/check-active-contract', [UnitController::class, 'checkActiveContract'])->name('penghuni.checkActiveContract');
    
    Route::get('/kontrak/create/{penghuni}', [KontrakController::class, 'create'])->name('kontrak.create');
    Route::post('/kontrak', [KontrakController::class, 'store'])->name('kontrak.store');
    Route::post('/kontrak/terminate/{kontrak}', [KontrakController::class, 'terminate'])->name('kontrak.terminate');
    Route::post('/akhiri-kontrak/{kontrak}', [KontrakController::class, 'terminate']);
    Route::get('/kontrak/{id}/cetak-sip', [KontrakController::class, 'cetakSip'])->name('kontrak.cetakSip');
    Route::get('/kontrak/{id}/edit-ba-keluar', [KontrakController::class, 'editBaKeluar'])->name('kontrak.editBaKeluar');
    Route::post('/kontrak/{id}/generate-pdf-ba-keluar', [KontrakController::class, 'generatePdfBaKeluar'])->name('kontrak.generatePdfBaKeluar');
    
    Route::post('/keluarga', [KeluargaController::class, 'store'])->name('keluarga.store');
    Route::put('/keluarga/{keluarga}', [KeluargaController::class, 'update'])->name('keluarga.update');
    Route::delete('/keluarga/{keluarga}', [KeluargaController::class, 'destroy'])->name('keluarga.destroy');

    Route::get('/unit', [UnitController::class, 'index'])->name('unit.index');
    Route::get('/unit/{kode_unit}', [UnitController::class, 'show'])->name('unit.show');
    
    Route::resource('tagihan', TagihanController::class);
    
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan');
    
    Route::get('/audit', [AuditController::class, 'index'])->name('audit');
});