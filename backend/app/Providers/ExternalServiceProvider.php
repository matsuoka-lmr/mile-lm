<?php

namespace App\Providers;

use App\Services\Trackimo;
use App\Services\Baremail;
use App\Services\BaremailTransport;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Mail;

class ExternalServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
        Mail::extend('baremail', function (array $config = []) {
            print_r($config);
            return new BaremailTransport();
        });
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bindIf(Trackimo::class, function () {
            return new Trackimo(config('services.trackimo'));
        });
        $this->app->bindIf(Baremail::class, function () {
            return new Baremail(config('services.baremail'));
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [Trackimo::class, Baremail::class];
    }
}
