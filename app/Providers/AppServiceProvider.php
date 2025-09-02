<?php

namespace App\Providers;

use App\Models\AccessAllow;
use App\Models\SaleDay;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Blade::directive('active', function ($routes) {
            return "<?php
                \$activeClasses = [];
                foreach ((array) {$routes} as \$route) {
                    if (Route::is(\$route)) {
                        \$activeClasses[] = 'current';
                    }
                }
                echo implode(' ', array_unique(\$activeClasses));
            ?>";
        });

        Blade::if('canCloseDay', function () {
            $todaySale = SaleDay::whereNull('end_time')->latest('start_time')->first();
            $accessAllowed = AccessAllow::first()?->allowed ?? false;

            return $todaySale && $accessAllowed;
        });

        Blade::directive("lastRate", function(){
            return "<?php echo \\App\\Models\\Currencie::latest()->value('currencie_value') ?? 0; ?>";
        });

        \Carbon\Carbon::setLocale("fr");
    }
}
