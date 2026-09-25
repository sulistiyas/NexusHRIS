<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;

abstract class BaseApiController extends Controller
{
    use ApiResponseTrait;
}
