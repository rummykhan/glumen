<?php

namespace Glumen\Authorization;

use Illuminate\Support\ServiceProvider;

class GlumenAuthServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->app->singleton('authorization', function ($app) {
            return new Authorization($app);
        });
    }
}