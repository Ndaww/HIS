<?php

namespace App\Providers;

use App\Models\MasterEquipmentType;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;

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
        // if (app()->environment('local')) {
        //     URL::forceScheme('https');
        // }

        View::composer('layouts.sidebar', function ($view) {
        $equipmentSidebar = MasterEquipmentType::all();
        $view->with('equipmentSidebar', $equipmentSidebar);
    });

    }

}
