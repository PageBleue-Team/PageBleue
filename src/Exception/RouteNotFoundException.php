<?php

namespace App\Exception;

class RouteNotFoundException extends \Exception
{
    /** @var int */
    protected $code = 404;
} 