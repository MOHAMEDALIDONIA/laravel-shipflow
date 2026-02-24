<?php

namespace Mohamedali\LaravelShipping\Traits;

use Mohamedali\LaravelShipping\Models\DeliveryInfo;

trait HasDeliveryInfo
{
    public function deliveryInfos()
    {
        return $this->morphMany(
            DeliveryInfo::class,
            'model'
        );
    }
}