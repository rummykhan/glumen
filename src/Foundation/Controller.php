<?php

namespace Glumen\Foundation;

use Glumen\Foundation\Traits\ServesFeaturesTrait;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use ServesFeaturesTrait;
}
