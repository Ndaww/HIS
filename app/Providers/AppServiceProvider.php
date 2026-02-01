<?php

namespace App\Providers;

use App\Models\MasterEquipmentType;
use Illuminate\Support\Facades\Http;
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
        // Http::macro('/zawa', function () {
        //     return Http::withOptions([
        //         'verify' => 'C:/xampp/apache/bin/curl-ca-bundle.crt',
        //     ]);
        // });

        View::composer('layouts.sidebar', function ($view) {
            $equipmentSidebar = MasterEquipmentType::all();
            $view->with('equipmentSidebar', $equipmentSidebar);

            // load count assigned ticket
            if (auth()->check()) {
                $ticketCount = auth()->user()->assignedTicketCount();
                $view->with('assigned_ticket_count', $ticketCount);
            }

        });
        
    }

    

}
