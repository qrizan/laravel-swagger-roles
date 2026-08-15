<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
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
        // Laravel membentuk URL absolut (asset(), link paginasi) dari host
        // request. Di stack docker-compose news-article ada DUA pemanggil
        // dengan host berbeda:
        //
        //   - browser lewat Traefik  -> Host: api.localhost   (benar)
        //   - SSR Next.js dari dalam container -> http://nginx-api (salah)
        //
        // Tanpa baris ini, respons yang sama bisa berisi https://nginx-api/...
        // — alamat internal Docker yang tidak berarti apa pun bagi browser,
        // sehingga gambar artikel gagal dimuat di situs publik.
        //
        // APP_URL adalah satu-satunya alamat kanonik API ini, jadi seluruh URL
        // dipaksa berakar di sana, siapa pun pemanggilnya.
        if (config('app.url')) {
            URL::forceRootUrl(config('app.url'));
        }
    }
}
