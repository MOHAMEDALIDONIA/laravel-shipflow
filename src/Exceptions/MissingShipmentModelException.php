<?php

namespace Mohamedali\LaravelShipping\Exceptions;

use Exception;

class MissingShipmentModelException extends Exception
{
    public function __construct()
    {
        parent::__construct(
            "Shipping operation aborted: No model specified.\n" .
                "You MUST call ->for(\$model) before calling processShipment().\n" .
                "Example: \$shipping->for(\$order)->processShipment(\$data);"
        );
    }
}
