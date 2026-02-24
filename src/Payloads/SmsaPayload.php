<?php

namespace Mohamedali\LaravelShipping\Payloads;

class SmsaPayload extends BasePayload
{
    public function driverName(): string
    {
        return 'smsa';
    }

    public function rules(): array
    {
        return [
            // Consignee Address
            'ConsigneeAddress'                      => 'required|array',
            'ConsigneeAddress.ContactName'           => 'required|string|max:255',
            'ConsigneeAddress.ContactPhoneNumber'    => 'required|string',
            'ConsigneeAddress.Coordinates'           => 'nullable|string',
            'ConsigneeAddress.Country'               => 'required|string|size:2',
            'ConsigneeAddress.City'                  => 'required|string',
            'ConsigneeAddress.AddressLine1'          => 'required|string',
            'ConsigneeAddress.AddressLine2'          => 'nullable|string',

            // Shipper Address
            'ShipperAddress'                         => 'required|array',
            'ShipperAddress.ContactName'             => 'required|string|max:255',
            'ShipperAddress.ContactPhoneNumber'      => 'required|string',
            'ShipperAddress.Coordinates'             => 'nullable|string',
            'ShipperAddress.Country'                 => 'required|string|size:2',
            'ShipperAddress.City'                    => 'required|string',
            'ShipperAddress.AddressLine1'            => 'required|string',

            // Order & Shipping Details
            'OrderNumber'        => 'required|string',
            'DeclaredValue'      => 'required|numeric|min:0',
            'CODAmount'          => 'nullable|numeric|min:0',
            'Parcels'            => 'nullable|integer|min:1',
            'ShipDate'           => 'nullable|string',
            'ShipmentCurrency'   => 'nullable|string|size:3',
            'WaybillType'        => 'nullable|string',
            'Weight'             => 'nullable|numeric|min:0',
            'WeightUnit'         => 'nullable|string|in:KG,LB',
            'ContentDescription' => 'nullable|string',
            'VatPaid'            => 'nullable|boolean',
            'DutyPaid'           => 'nullable|boolean',
            'ServiceCode'        => 'nullable|string',
        ];
    }

    public function defaults(): array
    {
        return [
            'ConsigneeAddress' => [
                'Coordinates'    => '24.6864257,46.6995142',
                'Country'        => 'SA',
                'AddressLine2'   => '',
            ],
            'ShipperAddress' => [
                'Coordinates'    => '24.6864257,46.6995142',
                'Country'        => 'SA',
            ],
            'CODAmount'          => 0,
            'Parcels'            => 1,
            'ShipmentCurrency'   => 'SAR',
            'WaybillType'        => 'PDF',
            'Weight'             => 1,
            'WeightUnit'         => 'KG',
            'ContentDescription' => 'Shipment',
            'VatPaid'            => true,
            'DutyPaid'           => false,
            'ServiceCode'        => 'EDDL',
        ];
    }
}
