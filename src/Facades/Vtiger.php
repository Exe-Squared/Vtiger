<?php

namespace Clystnet\Vtiger\Facades;

use Illuminate\Support\Facades\Facade;

class Vtiger extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'clystnet-vtiger';
    }
}