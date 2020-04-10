<?php

namespace btrsco\DomainParser\Facades;

use Illuminate\Support\Facades\Facade;

class DomainParser extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'domainparser';
    }
}
