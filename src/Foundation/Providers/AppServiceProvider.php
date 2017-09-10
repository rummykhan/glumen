<?php

namespace Glumen\Foundation\Providers;

use Glumen\Console\Commands\ControllerMakeCommand;
use Glumen\Console\Commands\CrudMakeCommand;
use Glumen\Console\Commands\FeatureMakeCommand;
use Glumen\Console\Commands\JobMakeCommand;
use Glumen\Console\Commands\ModelMakeCommand;
use Glumen\Console\Commands\OperationMakeCommand;
use Glumen\Console\Commands\ValidatorMakeCommand;
use Glumen\Foundation\Exceptions\Handler\JsonExceptionsHandler;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(
            ExceptionHandler::class,
            JsonExceptionsHandler::class
        );

        $this->commands(
            [
                ControllerMakeCommand::class,
                ModelMakeCommand::class,
                FeatureMakeCommand::class,
                OperationMakeCommand::class,
                JobMakeCommand::class,
                ValidatorMakeCommand::class,
                CrudMakeCommand::class,
            ]
        );
    }
}
