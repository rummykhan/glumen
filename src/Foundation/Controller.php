<?php

namespace Glumen\Foundation;

use Glumen\Foundation\Traits\ServesFeaturesTrait;
use App\Http\Controllers\Controller as BaseController;

class Controller extends BaseController
{
    use ServesFeaturesTrait;
}
