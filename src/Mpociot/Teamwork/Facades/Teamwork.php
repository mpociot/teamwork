<?php namespace Mpociot\Teamwork\Facades;

class Vault extends \Illuminate\Support\Facades\Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'teamwork';
    }
}