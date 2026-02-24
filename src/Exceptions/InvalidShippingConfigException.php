<?php

namespace Mohamedali\LaravelShipping\Exceptions;

class InvalidShippingConfigException extends \Exception
{
    public static function providerNotFound($provider)
    {
        return new self("Shipping provider [$provider] not found in config.");
    }
}
