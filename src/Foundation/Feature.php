<?php
namespace Glumen\Foundation;

use Glumen\Foundation\Traits\JobDispatcherTrait;
use Glumen\Foundation\Traits\MarshalTrait;

abstract class Feature
{
    use MarshalTrait;
    use JobDispatcherTrait;
}