<?php

namespace Mohamedali\LaravelShipping\Services\Drivers;

use Mohamedali\LaravelShipping\Services\BaseShippingService;
use Mohamedali\LaravelShipping\Exceptions\RequestFailedException;
use Mohamedali\LaravelShipping\Payloads\AramexPayload;
use Mohamedali\LaravelShipping\Data\ShipmentResponse;

class AramexDriver extends BaseShippingService
{
    public $mode;

    public function __construct()
    {
        $this->mode = config('shipping.providers.aramex.ENV', 'TEST');
    }

    protected function getProviderName(): string
    {
        return 'aramex';
    }

    protected function getPayloadClass(): string
    {
        return AramexPayload::class;
    }

    protected function preparePayload(array $data): array
    {
        
        return $data;
    }

    protected function sendRequest(array $payload): ShipmentResponse
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://ws.aramex.net/ShippingAPI.V2/Shipping/Service_1_0.svc/json/CreateShipments',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json',
            ],
        ]);

        $response = curl_exec($curl);
        $curlError = curl_error($curl);
        curl_close($curl);

        if ($curlError) {
            throw new RequestFailedException("Aramex CURL Error: " . $curlError);
        }

        $decoded = json_decode($response, true);
        if (empty($decoded)) {
            throw new RequestFailedException("Aramex API returned empty response.");
        }

        $hasErrors = !empty($decoded['HasErrors']);

        if ($hasErrors) {
            $notifications = $decoded['Notifications'] ?? [];
            if (empty($notifications)) {
                $notifications = $decoded['Shipments'][0]['Notifications'] ?? [];
            }
            throw new RequestFailedException("Aramex API Error: " . json_encode($notifications));
        }

        $processedShipment = $decoded['Shipments'][0] ?? null;

        return ShipmentResponse::make(
            trackingNumber: $processedShipment['ID'] ?? '',
            labelUrl: $processedShipment['ShipmentLabel']['LabelURL'] ?? null,
            rawResponse: $decoded
        );
    }
}
