<?php

namespace Glumen\Authorization;

use Glumen\Authorization\Gateway\Kong\Auth;
use Glumen\Foundation\Exceptions\Exception;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $authGroup = 'glumen-auth';

    public function register()
    {
        $this->publishAuthConfiguration();

        // Bind Authorization to container
        $this->app->singleton('authorization', function ($app) {
            return new Authorization($app);
        });

        $guard = $this->getDefaultGuard();
        if (!$guard) {
            throw new Exception(trans("No guard specified in config/auth.php"));
        }

        $config = $this->getConfig()['guards'][$guard];

        // Bind Glumen Authentication to Container..
        $this->app->singleton('glumen.auth', function ($app) use ($config) {
            return new Auth($app, $config);
        });
    }

    private function publishAuthConfiguration()
    {
        $this->publishes([
            __DIR__ . '/../../config/auth.php' => $this->app->basePath() . '/config/auth.php',
        ], $this->authGroup);
    }

    public function boot()
    {
        $guard = $this->getDefaultGuard();

        if (!$guard) {
            throw new Exception(trans("No guard specified in config/auth.php"));
        }

        $this->app['auth']->viaRequest($guard, function ($request) {
            return $this->app->make('glumen.auth')->handle($request);
        });
    }

    /**
     * Load Auth Configuration if it is lumen,
     * In Laravel config are automatically loaded in config directory.
     */
    protected function configureAuth()
    {
        if (str_contains(app()->version(), 'Lumen')) {
            $this->app->configure('auth');
        }
    }

    public function getConfig()
    {
        $this->configureAuth();

        return config('auth');
    }

    protected function getDefaultGuard()
    {
        return array_get($this->getConfig(), 'defaults.guard', null);
    }
}