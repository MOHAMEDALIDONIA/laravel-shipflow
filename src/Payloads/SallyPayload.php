<?php

namespace Mohamedali\LaravelShipping\Payloads;

class SallyPayload extends BasePayload
{
    public function driverName(): string
    {
        return 'sally';
    }

    public function rules(): array
    {
        return [
            // Reference & Payment
            'reference'            => 'required|string',
            'cod_amount'           => 'required|numeric|min:0',
            'cod_currency'         => 'required|string|size:3',

            // Customer
            'customer_name'        => 'required|string|max:255',
            'customer_phone'       => 'required|string',
            'customer_address'     => 'required|string',
            'customer_national_address' => 'required|string',
            'customer_city'        => 'required|string',
            'customer_country'     => 'nullable|string',

            // Dimensions
            'dimension_height'     => 'nullable|numeric|min:0',
            'dimension_length'     => 'nullable|numeric|min:0',
            'dimension_width'      => 'nullable|numeric|min:0',

            // Items
            'item_description'     => 'nullable|string',
            'number_of_pieces'     => 'nullable|integer|min:1',

            // Sender
            'sender_name'          => 'required|string|max:255',
            'sender_phone'         => 'required|string',
            'sender_address'       => 'required|string',
            'sender_national_address' => 'nullable|string',
            'sender_city'          => 'nullable|string',
            'sender_city_code'     => 'nullable|string',
            'sender_country'       => 'nullable|string',

            // Shipping
            'shipping_price'       => 'required|numeric|min:0',
            'weight_value'         => 'nullable|numeric|min:0',

            // Payment Type
            'paid'                 => 'nullable|in:0,1',
            'selectedPaymentType'  => 'nullable|string|in:PREPAID,COD',
        ];
    }

    public function defaults(): array
    {
        return [
            'cod_currency'           => 'SAR',
            'customer_country'       => 'Saudi Arabia',
            'customer_national_address' => '',
            'dimension_height'       => 10.5,
            'dimension_length'       => 20.0,
            'dimension_width'        => 15.0,
            'item_description'       => 'Shipment',
            'number_of_pieces'       => 1,
            'sender_national_address' => '',
            'sender_city'            => 'Jeddah',
            'sender_city_code'       => 'JED',
            'sender_country'         => 'Saudi Arabia',
            'weight_value'           => 1,
            'paid'                   => 0,
            'selectedPaymentType'    => 'COD',
        ];
    }
}
