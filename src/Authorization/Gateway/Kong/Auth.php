<?php

namespace Glumen\Authorization\Gateway\Kong;

use Glumen\Authorization\Gateway\Contracts\AuthContract;
use Glumen\Foundation\Exceptions\Exception;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request;

/**
 * Class Auth
 *
 * @package Awok\Autrhorization\Gateway\Kong
 */
class Auth implements AuthContract
{
    /**
     * @var Container $app
     */
    protected $app;

    /**
     * @var array
     */
    protected $config;

    public function __construct(Container $app, array $config)
    {
        $this->app = $app;
        $this->config = $config;
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return int|null authenticated userid or null
     */
    public function handle(Request $request)
    {
        if ($this->config['driver'] === 'kong') {
            return $this->kongHandleRequest($request);
        }

        return $this->baseHandleRequest($request);
    }

    protected function kongHandleRequest(Request $request)
    {
        if (
            !$request->headers->has('x-anonymous-consumer')
            && $request->headers->has('Authorization')
            && $request->headers->has('x-consumer-id')
            && $request->headers->has('x-authenticated-userid')
        ) {
            return $this->getModel()->find($request->headers->get('x-authenticated-userid'));
        }

        return null;
    }

    protected function baseHandleRequest(Request $request)
    {
        $headersKey = $this->config['headers_key'] ?? 'token';
        $tableKey = $this->config['table_key'] ?? 'token';

        $token = $request->headers->get($headersKey);

        if (!empty($token)) {
            return $this->getModel()->where($tableKey, $token)->first();
        }

        return null;
    }

    protected function getModel()
    {
        if (!isset($this->config['model']) || empty($this->config['model'])) {
            throw new Exception(trans('No model given for Kong Auth configuration.'));
        }

        if (!class_exists($this->config['model'])) {
            throw new Exception(trans("Please make sure [{$this->config['model']}] exists."));
        }

        return new $this->config['model'];
    }
}