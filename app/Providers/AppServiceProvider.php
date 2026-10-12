<?php

namespace App\Providers;

use App\Models\Kendala;
use App\Models\Pengadaan;
use App\Models\Realisasi;
use App\Models\Target;
use App\Models\User;
use App\Observers\AuditableObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Register observer manual untuk model yang butuh audit
        Target::observe(AuditableObserver::class);
        Realisasi::observe(AuditableObserver::class);
        Pengadaan::observe(AuditableObserver::class);
        Kendala::observe(AuditableObserver::class);
        User::observe(AuditableObserver::class); 
    }
}