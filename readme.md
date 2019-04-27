# DNB Laravel API Client

[![Latest Version on Packagist](https://img.shields.io/packagist/v/e2consult/dnb-client.svg)](https://packagist.org/packages/e2consult/dnb-client)
[![Quality Score](https://img.shields.io/scrutinizer/g/e2-consult/dnb-laravel-client.svg)](https://scrutinizer-ci.com/g/e2-consult/dnb-laravel-client)
[![License](https://img.shields.io/packagist/l/e2consult/dnb-client.svg)](https://packagist.org/packages/e2consult/dnb-client)
[![Total Downloads](https://img.shields.io/packagist/dt/e2consult/dnb-client.svg)](https://packagist.org/packages/e2consult/dnb-client)
[![StyleCI](https://styleci.io/repos/181854402/shield)](https://styleci.io/repos/181854402)

E2Consult is a webdevelopment team based in Oslo, Norway. You'll find more information about us [on our website](https://e2consult.no).

This package is made to easily communicate with DNBs API using PHP and Laravel, [read more about the API](https://github.com/DNBbank/getting-started).


## Installation

You can install the package via composer:

```bash
composer require e2consult/dnb-client
```

Then you need to set your credentials in the .env file, and add the following array to your config/services.php file.

``` php
    'dnb' => [
        'client_id'     => env('DNB_CLIENT_ID'),
        'client_secret' => env('DNB_CLIENT_SECRET'),
        'api_key'       => env('DNB_API_KEY'),
        'region'       => env('DNB_REGION', 'eu-west-1'),
        'service'       => env('DNB_SERVICE', 'execute-api'),
        'endpoint'       => env('DNB_ENDPOINT', 'https://developer-api-sandbox.dnb.no'),
    ],
```

## Usage

To get going you only need to pass the relevant customer ID when creating the client.

``` php

    use E2Consult\DNBApiClient\Client;

    $client = new Client($customerId);

    // Customer
    $client->getCustomerDetails();

    // Accounts
    $client->getAccounts();
    $client->getAccountDetails($accountNumber);
    $client->getAccountBalance($accountNumber);
    $client->getAccountTransactions($accountNumber, $from, $to);

    // Payments
    $client->initiatePayment($debitAccountNumber, $creditAccountNumber, $amount, $requestedExecutionDate);
    $client->updatePayment($accountNumber, $paymentId, $debitAccountNumber, $amount, $status, $requestedExecutionDate);
    $client->deletePayment($accountNumber, $paymentId);
    $client->getDuePayments($accountNumber);
    $client->getDuePayment($accountNumber, $paymentId);

    // Currencies
    $client->getCurrencyRates();
    $client->convertCurrency($targetCurrency);

    // Location
    $client->getBranches();
    $client->getBranch($branchId);
    $client->getATMs();

    $client->getNearestBranch($address);
    // or
    $client->getNearestBranch([$latitude, $longitude]);
    $client->getNearestATM($latitude = 0, $longitude = 0);
```

## License

The MIT License (MIT).
