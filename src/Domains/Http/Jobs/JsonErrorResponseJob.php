<?php
namespace Glumen\Domains\Http\Jobs;

use Glumen\Foundation\Job;
use Laravel\Lumen\Http\ResponseFactory;

class JsonErrorResponseJob extends Job
{
    protected $status;

    protected $content;

    protected $headers;

    protected $options;

    public function __construct($message = 'An error occurred', $code = 400, $status = 400, $headers = [], $options = 0)
    {
        $this->content = [
            'status' => $status,
            'error'  => [
                'code'    => $code,
                'message' => str_contains($message,'GlumenInputException') ? array_except(json_decode($message, true), ['GlumenInputException']) : $message,
            ],
        ];
        $this->status  = $status;
        $this->headers = $headers;
        $this->options = $options;
    }

    public function handle()
    {
        return response()->json($this->content, $this->status, $this->headers, $this->options);
    }
}