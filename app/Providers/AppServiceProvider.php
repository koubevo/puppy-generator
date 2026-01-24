<?php

namespace App\Providers;

use App\ContentProviders\PuppyContentProvider;
use App\Contracts\ContentProvider;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ContentProvider::class, PuppyContentProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
