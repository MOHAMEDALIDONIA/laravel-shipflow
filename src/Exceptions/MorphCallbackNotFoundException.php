<?php

namespace Mohamedali\LaravelShipping\Exceptions;

use Exception;

class MorphCallbackNotFoundException extends Exception
{
    public function __construct()
    {
        parent::__construct(
            "No morph callback found. You must either:\n" .
                "  1. Call ->onSuccess(\$callback) before processShipment()\n" .
                "  2. Set 'on_success_callback' in config/shipping.php\n" .
                "Without one of these, the delivery info cannot be stored."
        );
    }
}
