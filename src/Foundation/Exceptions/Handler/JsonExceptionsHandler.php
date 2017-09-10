<?php

namespace Glumen\Foundation\Exceptions\Handler;

use Glumen\Domains\Http\Jobs\JsonErrorResponseJob;
use Glumen\Foundation\Traits\JobDispatcherTrait;
use Glumen\Foundation\Traits\MarshalTrait;
use Exception;
use Laravel\Lumen\Exceptions\Handler;

class JsonExceptionsHandler extends Handler
{
    use MarshalTrait;
    use JobDispatcherTrait;

    public function report(Exception $e)
    {
        parent::report($e);
    }

    public function render($request, Exception $e)
    {
        return $this->run(JsonErrorResponseJob::class, [
            'message' => $e->getMessage(),
            'code'    => get_class($e),
            'status'  => ($e->getCode() < 100 || $e->getCode() >= 600) ? 400 : $e->getCode(),
        ]);
    }
}