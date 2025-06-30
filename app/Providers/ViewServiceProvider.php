<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        // Only register blade directives after everything is loaded
        $this->app->booted(function () {
            // Auto-register cart components
            Blade::componentNamespace('App\\View\\Components\\Cart', 'cart');
            Blade::componentNamespace('App\\View\\Components\\UI', 'ui');

            // Custom Blade directives for UK store
            Blade::directive('price', function ($expression) {
                return "<?php
                    \$value = ({$expression});
                    if (\$value instanceof \App\ValueObjects\Money) {
                        echo \$value->format();
                    } else {
                        echo '£' . number_format(\$value, 2);
                    }
                ?>";
            });

            Blade::directive('cartCount', function () {
                return "<?php echo app(\App\Services\CartService::class)->getItemCount(); ?>";
            });
        });
    }
}
