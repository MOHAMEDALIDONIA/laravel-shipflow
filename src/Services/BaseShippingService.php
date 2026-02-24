<?php

namespace Mohamedali\LaravelShipping\Services;

use Mohamedali\LaravelShipping\Contracts\ShippingService;
use Mohamedali\LaravelShipping\Models\DeliveryInfo;
use Mohamedali\LaravelShipping\Data\ShipmentResponse;
use Mohamedali\LaravelShipping\Exceptions\MorphCallbackNotFoundException;
use Mohamedali\LaravelShipping\Exceptions\MissingShipmentModelException;
use Illuminate\Database\Eloquent\Model;

abstract class BaseShippingService implements ShippingService
{
    /**
     * The model instance for morph relationship (Subject of shipment).
     */
    protected ?Model $model = null;

    /**
     * Temporary per-request success callback.
     */
    protected $successCallback = null;

    /**
     * Get the short name of the provider (e.g., 'sally', 'aramex').
     */
    abstract protected function getProviderName(): string;

    /**
     * Get the payload class for this driver.
     */
    abstract protected function getPayloadClass(): string;

    /**
     * Prepare the final API payload.
     * Receives validated data from the Payload class.
     */
    abstract protected function preparePayload(array $data): array;

    /**
     * Execute the request to the shipping API.
     */
    abstract protected function sendRequest(array $payload): ShipmentResponse;

    /**
     * Set the model for morph relationship.
     * Extracts model_type and model_id automatically.
     *
     * @param Model $model
     * @return static
     */
    public function for(Model $model): static
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Set a one-time callback for a successful shipment.
     *
     * @param callable $callback
     * @return static
     */
    public function onSuccess(callable $callback): static
    {
        $this->successCallback = $callback;

        return $this;
    }

    /**
     * Get the latest label URL for the given model from this provider.
     */
    public function getLabel(Model $model): ?string
    {
        $info = $model->deliveryInfos()
            ->where('provider', $this->getProviderName())
            ->where('status', true)
            ->latest()
            ->first();

        return $info?->info['label_url'] ?? null;
    }

    /**
     * Process the shipment.
     * Execution flow: Validate -> Prepare -> Send -> Handle Result -> Reset State.
     */
    public function processShipment(array $data): bool
    {
        $this->ensureModelIsSet();

        try {
            // 1. Validate via Payload Object
            $payloadClass = $this->getPayloadClass();
            $payload = new $payloadClass($data);
            $validatedData = $payload->validate()->toArray();

            // 2. Prepare & Send API Request
            $apiPayload = $this->preparePayload($validatedData);
            $response = $this->sendRequest($apiPayload);

            // 3. Persist and trigger logic
            return $this->handleResult($response);
        } finally {
            $this->resetState();
        }
    }

    /**
     * Persist successful delivery info and execute callbacks.
     */
    protected function handleResult(ShipmentResponse $response): bool
    {
        if (!$response->success) {
            return false;
        }

        // Persist DB entry
        if ($this->model) {
            DeliveryInfo::create([
                'model_type' => get_class($this->model),
                'model_id'   => $this->model->getKey(),
                'provider'   => $this->getProviderName(),
                'status'     => true,
                'info'       => $response->toArray(),
            ]);
        }

        // Execute logic chain
        $this->executeSuccessCallback($response);

        return true;
    }

    /**
     * Execute completion logic after successful shipment.
     * Priority: Closure -> Global Config -> Error
     */
    protected function executeSuccessCallback(ShipmentResponse $response): void
    {
        if ($this->successCallback && is_callable($this->successCallback)) {
            call_user_func($this->successCallback, $this->model, $response->toArray(), $this->getProviderName());
        }
    }

    
    /**
     * Reset the service state after each call to prevent data leakage.
     */
    protected function resetState(): void
    {
        $this->model = null;
        $this->successCallback = null;
    }

    /**
     * Guard to ensure a model is always attached before processing.
     */
    protected function ensureModelIsSet(): void
    {
        if (!$this->model) {
            throw new MissingShipmentModelException();
        }
    }
}
