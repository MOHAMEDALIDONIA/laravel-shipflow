<?php

return [


    'providers' => [
        'aramex' => [
            'driver' => \Mohamedali\LaravelShipping\Services\Drivers\AramexDriver::class,
            'ENV' => env('ARAMEX_ENV', 'TEST'),
            'TEST' => [
                'UserName' => env('ARAMEX_USER_NAME'),
                'Password' => env('ARAMEX_PASSWORD'),
                'AccountNumber' => env('ARAMEX_ACCOUNT_NUMBER'),
                'AccountPin' => env('ARAMEX_ACCOUNT_PIN'),
                'AccountEntity' => env('ARAMEX_ACCOUNT_ENTITY'),
                'AccountCountryCode' => env('ARAMEX_ACCOUNT_COUNTRY_CODE', 'SA'),
            ],
            'LIVE' => [
                'UserName' => env('ARAMEX_USER_NAME'),
                'Password' => env('ARAMEX_PASSWORD'),
                'AccountNumber' => env('ARAMEX_ACCOUNT_NUMBER'),
                'AccountPin' => env('ARAMEX_ACCOUNT_PIN'),
                'AccountEntity' => env('ARAMEX_ACCOUNT_ENTITY'),
                'AccountCountryCode' => env('ARAMEX_ACCOUNT_COUNTRY_CODE', 'SA'),
            ],
            'ProductGroup' => 'DOM',
            'ProductType' => 'ONP',
            'Payment' => 'P',
            'PaymentOptions' => 'ACCT',
            'LabelInfo' => [
                'ReportID' => 9729,
                'ReportType' => 'URL',
            ],
        ],
        'sally' => [
            'driver' => \Mohamedali\LaravelShipping\Services\Drivers\SallyDriver::class,
            'base_url' => env('SALLY_BASE_URL'),
            'partner_code' => env('SALLY_PARTNER_CODE'),
        ],
        'smsa' => [
            'driver' => \Mohamedali\LaravelShipping\Services\Drivers\SmsaDriver::class,
            'base_url' => env('SMSA_BASE_URL'),
            'api_key' => env('SMSA_API_KEY'),
        ],
    ],
];
