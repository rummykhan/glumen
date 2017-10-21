<?php

namespace Glumen\Console\Commands;

use Illuminate\Console\Command;

class RouteListCommand extends Command
{
    const METHOD = 'method';

    const URI = 'uri';

    const ACTION = 'action';

    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'glumen:route-list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List routes in lumen.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $routes = $this->getRoutes();

        $this->listRoutes($routes);
    }

    protected function listRoutes(array $routes)
    {
        $this->table(['#', 'method', 'uri', 'action'], $routes);
    }

    /**
     * @return array
     */
    protected function getRoutes()
    {
        $counter = 0;

        return collect(app()->getRoutes())->map(function (array $route) use (&$counter) {
            return array_merge(array_only($route, [
                ++$counter,
                static::METHOD,
                static::URI,
            ]), $this->getAction($route[static::ACTION]));
        })->toArray();
    }

    protected function getAction($action)
    {
        /**
         * @var \Closure $action
         */
        $action = is_array($action) && ! isset($action['uses']) ? $action[0] : $action;

        if ($action instanceof \Closure) {
            return ['action' => 'Closure'];
        }

        return ['action' => $action['uses']];
    }
}
