<?php

namespace Mohamedali\LaravelShipping\Services\Drivers;

use Mohamedali\LaravelShipping\Services\BaseShippingService;
use Mohamedali\LaravelShipping\Exceptions\RequestFailedException;
use Mohamedali\LaravelShipping\Payloads\SmsaPayload;
use Mohamedali\LaravelShipping\Data\ShipmentResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class SmsaDriver extends BaseShippingService
{
    protected $baseUrl;
    protected $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('shipping.providers.smsa.base_url');
        $this->apiKey = config('shipping.providers.smsa.api_key');
    }

    protected function getProviderName(): string
    {
        return 'smsa';
    }

    protected function getPayloadClass(): string
    {
        return SmsaPayload::class;
    }

    protected function client()
    {
        return Http::timeout(30)
            ->withHeaders([
                "apikey" => $this->apiKey,
                "Content-Type" => "application/json",
            ]);
    }

    protected function preparePayload(array $data): array
    {
        return $data;
    }

    protected function sendRequest(array $payload): ShipmentResponse
    {
        $response = $this->client()->post($this->baseUrl . '/shipment/b2c/new', $payload);

        if (!$response->successful()) {
            throw new RequestFailedException("Smsa API Request Failed: " . $response->body());
        }

        $data = $response->json();
        $awb = $data['awb'] ?? $data['sawb'] ?? null;
        $awbFile = $data['waybills'][0]['awbFile'] ?? null;

        if ($awb && $awbFile) {
            $pdfContent = base64_decode(trim($awbFile));
            $fileName = "smsa-label-{$awb}.pdf";
            $filePath = "labels/smsa/{$fileName}";

            Storage::disk('public')->put($filePath, $pdfContent);
            $labelUrl = Storage::disk('public')->url($filePath);

            unset($data['waybills']);

            return ShipmentResponse::make(
                trackingNumber: $awb,
                labelUrl: $labelUrl,
                rawResponse: $data
            );
        }

        throw new RequestFailedException("Smsa API Response missing AWB or PDF.");
    }
}
