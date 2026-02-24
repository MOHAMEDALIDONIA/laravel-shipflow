<?php

namespace Mohamedali\LaravelShipping\Services;

use Mohamedali\LaravelShipping\Exceptions\DriverNotFoundException;

class ShippingManager
{
    public function driver(string $name)
    {
        $config = config("shipping.providers.$name");

        if (!$config || !isset($config['driver'])) {
            throw new DriverNotFoundException("Shipping provider [$name] not found or missing driver configuration.");
        }

        return app($config['driver']);
    }
}
