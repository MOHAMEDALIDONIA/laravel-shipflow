<?php

namespace Mohamedali\LaravelShipping\Data;

/**
 * Data Transfer Object for shipping API responses.
 * Ensures consistent handling of results across all drivers.
 */
class ShipmentResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly ?string $trackingNumber = null,
        public readonly ?string $labelUrl = null,
        public readonly array $rawResponse = []
    ) {}

    /**
     * Create a successful response.
     */
    public static function make(string $trackingNumber, ?string $labelUrl = null, array $rawResponse = []): self
    {
        return new self(
            success: true,
            trackingNumber: $trackingNumber,
            labelUrl: $labelUrl,
            rawResponse: $rawResponse
        );
    }

    /**
     * Create a failure response.
     */
    public static function failure(array $rawResponse = []): self
    {
        return new self(
            success: false,
            rawResponse: $rawResponse
        );
    }

    /**
     * Convert the response to an array for older compatibility or storage.
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'tracking_number' => $this->trackingNumber,
            'label_url' => $this->labelUrl,
            'response' => $this->rawResponse,
        ];
    }
}
