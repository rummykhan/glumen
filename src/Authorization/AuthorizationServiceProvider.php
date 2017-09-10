<?php

namespace Glumen\Authorization;

use Glumen\Authorization\Gateway\Kong\Auth;
use Illuminate\Support\ServiceProvider;

class AuthorizationServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->publishes([
            __DIR__ . '/../../config/auth.php' => $this->app->basePath().'/config/auth.php',
        ], 'auth');

        /*$this->app->singleton('authorization', function ($app) {
            return new Authorization($app);
        });

        $this->app->singleton('awok.authorization.kong.auth', function ($app) {
            return new Auth($app);
        });*/
    }


    public function boot()
    {
        $this->updateConfig();
        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.

        $this->app['auth']->viaRequest('api', function (Request $request) {
            if ($request->headers->has($this->getTokenKeyInHeader())) {
                return $this->model->where($this->getTokenKeyInHeader(), $request->headers->get($this->getTokenKey()))->first();
            }

            return null;
        });
    }

    protected function updateConfig()
    {
        $source = realpath(__DIR__ . '/../../config/auth.php');
    }

    public function kongBoot()
    {
        $this->app['auth']->viaRequest('api', function ($request) {
            $auth = $this->app->make(config('security.authorization_service', 'awok.authorization.kong.auth'));

            return $auth->handle($request);
        });
    }
}