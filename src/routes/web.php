<?php

use App\Shipping\Payloads\AramexOrderPayload;
use App\Shipping\Payloads\SallyOrderPayload;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Mohamedali\LaravelShipping\Services\ShippingManager;

/*
|--------------------------------------------------------------------------
| Shipping Test Routes
|--------------------------------------------------------------------------
|
| Usage:
|   POST /shipping/test/{driver}
|   body: JSON payload array
|
|   GET /shipping/test/{driver}/sally-sample   → test with Sally sample payload
|   GET /shipping/test/{driver}/smsa-sample    → test with SMSA sample payload
|
*/

Route::get('/shipping/test/seed', function () {
    \App\Models\Shipment::updateOrCreate(['code' => 'SHIP-001'], [
        'client_name' => 'Generic Customer',
        'mobile' => '0500000001',
        'address' => '123 Test Ave',
        'price' => 150.00,
    ]);

    return "Test data seeded! Usage: /shipping/test/{driver}/sally-sample";
});

Route::get('/shipping/test/{driver}/sally-sample', function ($driver) {
    try {
        $manager = app(ShippingManager::class);
        $service = $manager->driver($driver);

        // Fetch or create a dummy model for testing (assuming App\Models\Shipment exists)
        $model = \App\Models\Shipment::first() ?? \App\Models\Shipment::create([
            'code' => 'TEST-' . rand(100, 999),
            'client_name' => 'Architecture Test',
            'mobile' => '0501112223',
            'address' => 'Refactor St',
            'price' => 100
        ]);

        // Sample Sally payload
        $payload = AramexOrderPayload::data($model);

        // Process with strict architecture
        $success = $service->for($model)
            ->processShipment($payload);

        // Retrieve label after success
        $label = $success ? $service->getLabel($model) : null;
   
        return response()->json([
            'driver'  => $driver,
            'success' => $success,
            'label'   => $label,
            'model'   => [
                'type' => get_class($model),
                'id'   => $model->id
            ]
        ]);
    } catch (\Mohamedali\LaravelShipping\Exceptions\PayloadValidationException $e) {
        return response()->json([
            'error'   => 'Validation Failed',
            'errors'  => $e->getErrors(),
        ], 422);
    } catch (\Mohamedali\LaravelShipping\Exceptions\MissingShipmentModelException $e) {
        return response()->json([
            'error' => 'Architectural Error',
            'message' => $e->getMessage()
        ], 400);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

Route::get('/shipping/test/validate/{driver}', function ($driver) {
    try {
        $manager = app(ShippingManager::class);
        $service = $manager->driver($driver);

        // Intentionally incomplete payload to trigger validation
        $payload = [
            'customer_name' => 'Test',
            // Missing required fields...
        ];

        // This will throw PayloadValidationException
        // $service->for($someModel)->processShipment($payload);

        // For testing validation only, instantiate the payload class directly
        $payloadClasses = [
            'sally'  => \Mohamedali\LaravelShipping\Payloads\SallyPayload::class,
            'smsa'   => \Mohamedali\LaravelShipping\Payloads\SmsaPayload::class,
            'aramex' => \Mohamedali\LaravelShipping\Payloads\AramexPayload::class,
        ];

        if (!isset($payloadClasses[$driver])) {
            return response()->json(['error' => "Unknown driver: {$driver}"], 400);
        }

        $payloadClass = $payloadClasses[$driver];
        $payloadInstance = new $payloadClass($payload);
        $payloadInstance->validate();

        return response()->json(['message' => 'Validation passed']);
    } catch (\Mohamedali\LaravelShipping\Exceptions\PayloadValidationException $e) {
        return response()->json([
            'error'   => 'Validation Failed',
            'driver'  => $driver,
            'message' => $e->getMessage(),
            'errors'  => $e->getErrors(),
        ], 422);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
        ], 500);
    }
});
