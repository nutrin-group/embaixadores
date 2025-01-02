<?php

namespace App\Providers;

use App\Services\CommissionService;
use Illuminate\Support\ServiceProvider;

class CommissionServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(CommissionService::class, function ($app) {
            return new CommissionService();
        });
    }
}
