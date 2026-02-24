<?php

namespace Mohamedali\LaravelShipping\Payloads;

use Illuminate\Support\Facades\Validator;
use Mohamedali\LaravelShipping\Exceptions\PayloadValidationException;

abstract class BasePayload
{
    protected array $data;
    protected array $validated = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Define the validation rules for this payload.
     * Each key in the payload should have a validation rule.
     */
    abstract public function rules(): array;

    /**
     * Define default values for optional fields.
     */
    public function defaults(): array
    {
        return [];
    }

    /**
     * Get the driver name (used in error messages).
     */
    abstract public function driverName(): string;

    /**
     * Validate the payload data against the rules.
     *
     * @throws PayloadValidationException
     */
    public function validate(): self
    {
        // Merge defaults with provided data (data takes priority)
        $merged = array_replace_recursive($this->defaults(), $this->data);

        $validator = Validator::make($merged, $this->rules());

        if ($validator->fails()) {
            throw new PayloadValidationException(
                $validator->errors()->toArray(),
                $this->driverName()
            );
        }

        $this->validated = $merged;

        return $this;
    }

    /**
     * Get the validated payload as array (without model_type/model_id).
     */
    public function toArray(): array
    {
        if (empty($this->validated)) {
            $this->validate();
        }

        // Remove model_type and model_id from the payload sent to API
        $payload = $this->validated;
        unset($payload['model_type'], $payload['model_id']);

        return $payload;
    }

    /**
     * Get the raw data.
     */
    public function getData(): array
    {
        return $this->data;
    }
}
