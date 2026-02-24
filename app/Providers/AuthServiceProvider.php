<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use App\Providers\SupabaseUserProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any authentication / authorization services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Registrar el provider personalizado de Supabase
        Auth::provider('supabase', function ($app, array $config) {
            return new SupabaseUserProvider($app['hash'], $config['model']);
        });
    }
}
