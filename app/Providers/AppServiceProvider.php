<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use App\Models\Kontrak;
use App\Observers\KontrakObserver;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Carbon::setLocale('id');
        
        date_default_timezone_set('Asia/Jakarta');
        
        Blade::directive('rupiah', function ($expression) {
            return "<?php echo 'Rp ' . number_format($expression, 0, ',', '.'); ?>";
        });
        
        Blade::directive('tanggalIndo', function ($expression) {
            return "<?php echo \Carbon\Carbon::parse($expression)->translatedFormat('d F Y'); ?>";
        });

        Kontrak::observe(KontrakObserver::class);

        if (env('APP_ENV') !== 'local') {
            URL::forceScheme('https');
        }
    }
}