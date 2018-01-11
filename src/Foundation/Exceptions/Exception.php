<?php

namespace Glumen\Foundation\Exceptions;

use Illuminate\Validation\Validator;

class Exception extends \Exception
{
    public function __construct(
        $message = "",
        $code = 0,
        Exception $previous = null
    ) {

        if ($message instanceof Validator) {
            $message = json_encode(array_merge($message->messages()->toArray(),['GlumenInputException'=>true]));
        }

        parent::__construct($message, $code, $previous);
    }
}