<?php

namespace Mohamedali\LaravelShipping\Services\Drivers;

use Mohamedali\LaravelShipping\Services\BaseShippingService;
use Mohamedali\LaravelShipping\Exceptions\RequestFailedException;
use Mohamedali\LaravelShipping\Payloads\SallyPayload;
use Mohamedali\LaravelShipping\Data\ShipmentResponse;
use Illuminate\Support\Facades\Http;

class SallyDriver extends BaseShippingService
{
    protected $baseUrl;
    protected $partnerCode;

    public function __construct()
    {
        $this->baseUrl = config('shipping.providers.sally.base_url');
        $this->partnerCode = config('shipping.providers.sally.partner_code');
    }

    protected function getProviderName(): string
    {
        return 'sally';
    }

    protected function getPayloadClass(): string
    {
        return SallyPayload::class;
    }

    protected function client()
    {
        return Http::timeout(20)
            ->retry(3, 2000)
            ->withHeaders([
                "X-Partner-Code" => $this->partnerCode,
                "Content-Type" => "application/json",
            ]);
    }

    protected function preparePayload(array $data): array
    {
        return $data;
    }

    protected function sendRequest(array $payload): ShipmentResponse
    {
        $response = $this->client()->post($this->baseUrl . '/parcels/create', $payload);

        if (!$response->successful()) {
            throw new RequestFailedException("Sally API Request Failed: " . ($response->json()['message'] ?? $response->body()));
        }

        $result = $response->json();

        if (is_string($result)) {
            $result = json_decode($result, true) ?: $result;
        }

        $trackingNumber = $result['reference_number'] ?? $result['tracking_number'] ?? null;
        $labelUrl = $result['Receipt'] ?? $result['label_url'] ?? null;

        if ($trackingNumber) {
            return ShipmentResponse::make(
                trackingNumber: $trackingNumber,
                labelUrl: $labelUrl,
                rawResponse: $result
            );
        }

        throw new RequestFailedException("Sally API Success Response missing tracking number.");
    }
}
