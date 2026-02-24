<?php

namespace Mohamedali\LaravelShipping\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class MakeShippingPayload extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:shipping-payload';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new shipping payload class with example data';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // 1. Select Company
        $company = $this->choice(
            'Which shipping company do you want to create a payload for?',
            ['Sally', 'SMSA', 'Aramex'],
            0
        );

        // 2. Ask for Class Name
        $defaultName = $company . 'OrderPayload';
        $name = $this->ask('Enter the name for the payload class', $defaultName);

        if (empty($name)) {
            $this->error('Class name is required.');
            return self::FAILURE;
        }

        $className = Str::studly($name);
        $fileName = $className . '.php';
        $directory = app_path('Shipping/Payloads');
        $path = $directory . '/' . $fileName;

        if (File::exists($path)) {
            if (!$this->confirm("File [{$fileName}] already exists. Overwrite?")) {
                return self::SUCCESS;
            }
        }

        // 3. Prepare Data
        $data = $this->getExampleData(strtolower($company));

        // 4. Load Stub and Replace
        $stub = File::get(__DIR__ . '/../stubs/example-payload.stub');

        $content = str_replace(
            ['{{ namespace }}', '{{ class }}', '{{ company }}', '{{ data }}'],
            ['App\Shipping\Payloads', $className, $company, $this->formatArray($data)],
            $stub
        );

        // 5. Create Directory and File
        if (!File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        File::put($path, $content);

        $this->info("Successfully created: App\Shipping\Payloads\\{$className}");
        $this->comment("Usage: \$shipping->for(\$model)->processShipment({$className}::data());");

        return self::SUCCESS;
    }

    /**
     * Get example data based on driver.
     */
    protected function getExampleData(string $driver): array
    {
        return match ($driver) {
            'sally' => [
                   "reference" => "DEFAULT123-".rand(1000, 9999),
                    "cod_amount" => 150,
                    "cod_currency" => "SAR",

                    "customer_name" => "Default Customer",
                    "customer_phone" => "+966500000000",
                    "customer_address" => "Default Address",
                    "customer_national_address" => "0000000000",
                    "customer_city" => "Riyadh",
                    "customer_country" => "Saudi Arabia",

                    "dimension_height" => 10.5,
                    "dimension_length" => 20.0,
                    "dimension_width"  => 15.0,

                    "item_description" => "Shipment",
                    "number_of_pieces" => 1,

                    "sender_name" => "Default Sender",
                    "sender_phone" => "+966511111111",
                    "sender_address" => "Sender Address",
                    "sender_national_address" => "1111111111",
                    "sender_city" => "Jeddah",
                    "sender_city_code" => "JED",
                    "sender_country" => "Saudi Arabia",

                    "shipping_price" => 100,
                    "weight_value" => 1,

                    "paid" => 0,
                    "selectedPaymentType" => "COD",
            ],
            'smsa' => [
                    "ConsigneeAddress" => [
                    "ContactName" => "Default Customer",
                    "ContactPhoneNumber" => "966500000000",
                    "Coordinates" => "24.6864257,46.6995142",
                    "Country" => "SA",
                    "City" => "Jeddah",
                    "AddressLine1" => "Default Address",
                    "AddressLine2" => "",
                ],

                "ShipperAddress" => [
                    "ContactName" => "Default Shipper",
                    "ContactPhoneNumber" => "966511111111",
                    "Coordinates" => "24.6864257,46.6995142",
                    "Country" => "SA",
                    "City" => "Riyadh",
                    "AddressLine1" => "SMSA Express HQ",
                ],

                "OrderNumber" => "ORDER123456",
                "DeclaredValue" => 100.0,
                "CODAmount" => 0.0,
                "Parcels" => 1,
                "ShipDate" => now()->toIso8601String(), // أو قيمة ثابتة مثل: "2026-01-01T10:00:00+03:00"
                "ShipmentCurrency" => "SAR",
                "WaybillType" => "PDF",
                "Weight" => 1.0,
                "WeightUnit" => "KG",
                "ContentDescription" => "Shipment",
                "VatPaid" => true,
                "DutyPaid" => false,
                "ServiceCode" => "EDDL",
            ],
            'aramex' => [
                     

                'Shipments' => [
                    [
                        'Reference1' => 'Shipment-ORDER123',
                        'Reference2' => null,
                        'Reference3' => null,

                        'Shipper' => [
                            'Reference1' => 'Shipper-ORDER123',
                            'Reference2' => null,
                            'AccountNumber' => '123456',

                            'PartyAddress' => [
                                'Line1' => 'Sender Address',
                                'Line2' => '',
                                'Line3' => '',
                                'City' => 'Jeddah',
                                'StateOrProvinceCode' => 'SA',
                                'PostCode' => '000000',
                                'CountryCode' => 'SA',
                                'Longitude' => 39.1925,
                                'Latitude' => 21.4858,
                            ],

                            'Contact' => [
                                'Department' => null,
                                'PersonName' => 'Default Sender',
                                'Title' => null,
                                'CompanyName' => 'Sender Company',
                                'PhoneNumber1' => '0500000000',
                                'PhoneNumber1Ext' => '',
                                'PhoneNumber2' => '',
                                'PhoneNumber2Ext' => '',
                                'FaxNumber' => null,
                                'CellPhone' => '0500000000',
                                'EmailAddress' => 'noreply@example.com',
                                'Type' => '',
                            ],
                        ],

                        'Consignee' => [
                            'Reference1' => null,
                            'Reference2' => null,
                            'AccountNumber' => null,

                            'PartyAddress' => [
                                'Line1' => 'Receiver Address',
                                'Line2' => '',
                                'Line3' => '',
                                'City' => 'Riyadh',
                                'StateOrProvinceCode' => '',
                                'PostCode' => '',
                                'CountryCode' => 'SA',
                                'Longitude' => 46.6753,
                                'Latitude' => 24.7136,
                            ],

                            'Contact' => [
                                'Department' => null,
                                'PersonName' => 'Default Customer',
                                'Title' => null,
                                'CompanyName' => 'Customer Company',
                                'PhoneNumber1' => '0550000000',
                                'PhoneNumber1Ext' => '',
                                'PhoneNumber2' => '',
                                'PhoneNumber2Ext' => '',
                                'FaxNumber' => null,
                                'CellPhone' => '0550000000',
                                'EmailAddress' => 'customer@example.com',
                                'Type' => '',
                            ],
                        ],

                        'ThirdParty' => null,

                        'ShippingDateTime' => '/Date(' . (time() * 1000) . ')/',
                        'DueDate' => '/Date(' . ((time() + 86400) * 1000) . ')/',

                        'Comments' => 'Default shipment',
                        'PickupLocation' => null,
                        'OperationsInstructions' => null,
                        'AccountingInstrcutions' => null,

                        'Details' => [
                            'Dimensions' => [
                                'Length' => 10,
                                'Width' => 10,
                                'Height' => 10,
                                'Unit' => 'CM',
                            ],

                            'ActualWeight' => [
                                'Unit' => 'KG',
                                'Value' => 1,
                            ],

                            'ChargeableWeight' => [
                                'Unit' => 'KG',
                                'Value' => 1,
                            ],

                            'DescriptionOfGoods' => 'Items',
                            'GoodsOriginCountry' => 'SA',
                            'NumberOfPieces' => 1,

                            'ProductGroup' => 'DOM',
                            'ProductType' => 'ONP',
                            'PaymentType' => 'P',
                            'PaymentOptions' => 'ACCT',

                            'CustomsValueAmount' => [
                                'CurrencyCode' => 'SAR',
                                'Value' => 100,
                            ],

                            'CashOnDeliveryAmount' => [
                                'CurrencyCode' => 'SAR',
                                'Value' => 0,
                            ],

                            'InsuranceAmount' => [
                                'CurrencyCode' => 'SAR',
                                'Value' => 0,
                            ],

                            'CashAdditionalAmount' => [
                                'CurrencyCode' => 'SAR',
                                'Value' => 0,
                            ],

                            'CashAdditionalAmountDescription' => null,

                            'CollectAmount' => [
                                'CurrencyCode' => 'SAR',
                                'Value' => 0,
                            ],

                            'Services' => '',

                            'Items' => [
                                [
                                    'PackageType' => 'item',
                                    'Quantity' => 1,
                                    'Weight' => [
                                        'Unit' => 'KG',
                                        'Value' => 1,
                                    ],
                                    'Comments' => 'No description',
                                    'Reference' => 'ORDER123',
                                ],
                            ],

                            'DeliveryInstructions' => null,
                            'AdditionalProperties' => null,
                            'ContainsDangerousGoods' => false,
                        ],

                        'Attachments' => null,
                        'ForeignHAWB' => null,
                        'TransportType' => 0,
                        'PickupGUID' => null,
                        'Number' => null,
                        'ScheduledDelivery' => null,
                    ],
                ],

                'LabelInfo' => [
                    'ReportID' => 9729,
                    'ReportType' => 'URL',
                ],
                'ClientInfo' => [
                    'UserName' => config("shipping.providers.aramex.TEST.UserName"),
                    'Password' => config("shipping.providers.aramex.TEST.Password"),
                    'Version' => 'v1.0',
                    'AccountNumber' => config("shipping.providers.aramex.TEST.AccountNumber"),
                    'AccountPin' => config("shipping.providers.aramex.TEST.AccountPin"),
                    'AccountEntity' => config("shipping.providers.aramex.TEST.AccountEntity"),
                    'AccountCountryCode' => config("shipping.providers.aramex.TEST.AccountCountryCode"),
                    'Source' => 0,
                    'PreferredLanguageCode' => null,
                ],
            'Transaction' => null,
            ],
          
            default => [],
        };
    }

    /**
     * Format array for stub.
     */
    protected function formatArray(array $array): string
    {
        $code = var_export($array, true);
        $code = str_replace(['array (', ')'], ['[', ']'], $code);
        $code = preg_replace('/=> \s+\n\s+\[/', '=> [', $code);

        // Fix indentation for the stub
        $lines = explode("\n", $code);
        $formatted = array_map(function ($line, $index) {
            return ($index === 0) ? $line : "        " . $line;
        }, $lines, array_keys($lines));

        return implode("\n", $formatted);
    }
}
