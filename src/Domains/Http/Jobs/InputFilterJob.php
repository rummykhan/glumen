<?php

namespace Glumen\Domains\Http\Jobs;

use Glumen\Foundation\Exceptions\Exception;
use Glumen\Foundation\Http\Request;
use Glumen\Foundation\Job;

class InputFilterJob extends Job
{
    protected $expectedKeys = [];

    protected $input = [];

    public function __construct($expectedKeys = [])
    {
        if (!empty($expectedKeys)) {
            $this->expectedKeys = $expectedKeys;
        }
    }

    public function handle(Request $request)
    {
        if (empty($this->input)) {
            return [];
        }

        if (empty($this->expectedKeys)) {
            throw new Exception(trans("Expected keys cannot be empty array."));
        }

        return array_only($this->input, $this->expectedKeys);
    }
}