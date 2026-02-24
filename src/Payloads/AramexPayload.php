<?php

namespace Mohamedali\LaravelShipping\Payloads;

use Carbon\Carbon;

class AramexPayload extends BasePayload
{
    public function driverName(): string
    {
        return 'aramex';
    }

    public function rules(): array
    {
        return [
            // Shipment Reference
            'Shipments'                                          => 'required|array|min:1',
            'Shipments.*.Reference1'                             => 'required|string',

            // Shipper
            'Shipments.*.Shipper'                                => 'required|array',
            'Shipments.*.Shipper.Reference1'                     => 'nullable|string',
            'Shipments.*.Shipper.AccountNumber'                  => 'required|string',
            'Shipments.*.Shipper.PartyAddress'                   => 'required|array',
            'Shipments.*.Shipper.PartyAddress.Line1'             => 'required|string',
            'Shipments.*.Shipper.PartyAddress.City'              => 'required|string',
            'Shipments.*.Shipper.PartyAddress.CountryCode'       => 'required|string|size:2',
            'Shipments.*.Shipper.Contact'                        => 'required|array',
            'Shipments.*.Shipper.Contact.PersonName'             => 'required|string',
            'Shipments.*.Shipper.Contact.PhoneNumber1'           => 'required|string',

            // Consignee
            'Shipments.*.Consignee'                              => 'required|array',
            'Shipments.*.Consignee.PartyAddress'                 => 'required|array',
            'Shipments.*.Consignee.PartyAddress.Line1'           => 'required|string',
            'Shipments.*.Consignee.PartyAddress.City'            => 'required|string',
            'Shipments.*.Consignee.PartyAddress.CountryCode'     => 'required|string|size:2',
            'Shipments.*.Consignee.Contact'                      => 'required|array',
            'Shipments.*.Consignee.Contact.PersonName'           => 'required|string',
            'Shipments.*.Consignee.Contact.PhoneNumber1'         => 'required|string',

            // Details
            'Shipments.*.Details'                                => 'required|array',
            'Shipments.*.Details.ActualWeight'                   => 'required|array',
            'Shipments.*.Details.ActualWeight.Unit'              => 'required|string',
            'Shipments.*.Details.ActualWeight.Value'             => 'required|numeric|min:0',
            'Shipments.*.Details.DescriptionOfGoods'             => 'nullable|string',
            'Shipments.*.Details.NumberOfPieces'                 => 'required|integer|min:1',
            'Shipments.*.Details.ProductGroup'                   => 'required|string',
            'Shipments.*.Details.ProductType'                    => 'required|string',
            'Shipments.*.Details.PaymentType'                    => 'required|string',

            // Label Info
            'LabelInfo'                                          => 'nullable|array',
            'LabelInfo.ReportID'                                 => 'nullable|integer',
            'LabelInfo.ReportType'                               => 'nullable|string',

            // Client Info
            'ClientInfo'                                         => 'nullable|array',
            'ClientInfo.UserName'                                => 'nullable|string',
            'ClientInfo.Password'                                => 'nullable|string',
            'ClientInfo.AccountNumber'                           => 'nullable|string',
            'ClientInfo.AccountPin'                              => 'nullable|string',
            'ClientInfo.AccountEntity'                           => 'nullable|string',
            'ClientInfo.AccountCountryCode'                      => 'nullable|string',
        ];
    }

    public function defaults(): array
    {
        $mode = config('shipping.providers.aramex.ENV', 'TEST');

        return [
            'LabelInfo' => [
                'ReportID'   => config('shipping.providers.aramex.LabelInfo.ReportID', 9729),
                'ReportType' => config('shipping.providers.aramex.LabelInfo.ReportType', 'URL'),
            ],
            'ClientInfo' => [
                'UserName'           => config("shipping.providers.aramex.{$mode}.UserName"),
                'Password'           => config("shipping.providers.aramex.{$mode}.Password"),
                'Version'            => 'v1.0',
                'AccountNumber'      => config("shipping.providers.aramex.{$mode}.AccountNumber"),
                'AccountPin'         => config("shipping.providers.aramex.{$mode}.AccountPin"),
                'AccountEntity'      => config("shipping.providers.aramex.{$mode}.AccountEntity"),
                'AccountCountryCode' => config("shipping.providers.aramex.{$mode}.AccountCountryCode"),
                'Source'             => 0,
            ],
        ];
    }
}
