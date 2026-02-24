<?php

namespace Mohamedali\LaravelShipping\Contracts;

use Illuminate\Database\Eloquent\Model;

interface ShippingService
{
    /**
     * Set the model instance for the shipment.
     */
    public function for(Model $model): static;

    /**
     * Process the shipment with array payload.
     *
     * @param array $data The shipping payload data
     * @return bool
     */
    public function processShipment(array $data): bool;

    /**
     * Get the shipping label URL for a given model.
     */
    public function getLabel(Model $model): ?string;
}
