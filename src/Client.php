<?php

namespace E2Consult\DNBApiClient;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\HandlerStack;
use Illuminate\Support\Carbon;
use Aws\Credentials\Credentials;
use GuzzleHttp\Handler\CurlHandler;

class Client
{
    protected $customerId;
    protected $client;

    public function __construct($customerId = null)
    {
        $this->customerId = $customerId;
        $this->client = new GuzzleClient([
            'base_uri' => config('services.dnb.endpoint'),
            'handler' => tap(HandlerStack::create(new CurlHandler()), function ($stack) {
                $stack->push(new DNBRequestHandler(
                    new Credentials(
                        config('services.dnb.client_id'),
                        config('services.dnb.client_secret')
                    ),
                    config('services.dnb.api_key'),
                    $this->customerId
                ));
            })
        ]);
    }

    public function fetch($endpoint, $method = 'GET', $params = [])
    {
        return json_decode(
            $this->client
            ->request($method, $endpoint, $params)
            ->getBody()
        );
    }

    public function getCustomerDetails()
    {
        return $this->fetch('/customers/current');
    }

    public function getAccounts()
    {
        return $this->fetch('/accounts');
    }

    public function getAccountDetails($accountNumber)
    {
        return $this->fetch("/accounts/{$accountNumber}");
    }

    public function getAccountBalance($accountNumber)
    {
        return $this->fetch("/accounts/{$accountNumber}/balance");
    }

    public function getAccountTransactions($accountNumber, $from = null, $to = null)
    {
        $from   = $from ? Carbon::parse($from)  : now()->subMonth();
        $to     = $to   ? Carbon::parse($to)    : now();
        return $this->fetch("/transactions/{$accountNumber}", 'GET', [
            'query' => [
                'fromDate'  => $from->toDateString(),
                'toDate'    => $to->toDateString()
            ]
        ]);
    }

    public function initiatePayment($debitAccountNumber, $creditAccountNumber, $amount, $requestedExecutionDate = null)
    {
        $requestedExecutionDate = Carbon::parse($requestedExecutionDate ?? now());
        return $this->fetch("/payments", 'POST', [
            'body' => json_encode([
                'debitAccountNumber' =>  $debitAccountNumber,
                'creditAccountNumber' =>  $creditAccountNumber,
                'amount' =>  $amount,
                'requestedExecutionDate' =>  $requestedExecutionDate->toDateString()
            ])
        ]);
    }

    public function updatePayment($accountNumber, $paymentId, $debitAccountNumber, $amount, $status, $requestedExecutionDate = null)
    {
        return $this->fetch("/payments/{$accountNumber}/pending-payments//$paymentId", 'PATCH', [
            'body' => json_encode([
                'debitAccountNumber' =>  $debitAccountNumber,
                'amount' =>  $amount,
                'status' => $status,
                'requestedExecutionDate' =>  $requestedExecutionDate->toDateString(),
            ])
        ]);
    }

    public function deletePayment($accountNumber, $paymentId)
    {
        return $this->fetch("/payments/{$accountNumber}/pending-payments//$paymentId", 'DELETE');
    }

    public function getDuePayments($accountNumber, $paymentId = null)
    {
        return $this->fetch("/payments/{$accountNumber}/due/$paymentId");
    }

    public function getDuePayment($accountNumber, $paymentId)
    {
        return $this->getDuePayments($accountNumber, $paymentId);
    }

    public function getCurrencyRates()
    {
        return $this->fetch('currencies/NOK');
    }

    public function convertCurrency($targetCurrency)
    {
        return $this->fetch("currencies/NOK/convert/{$targetCurrency}");
    }

    public function getBranches($branchId = null)
    {
        return $this->fetch("/locations/branches/{$branchId}");
    }

    public function getBranch($branchId)
    {
        return $this->getBranches($branchId);
    }

    public function getATMs()
    {
        return $this->fetch('/locations/atms');
    }

    public function getNearestBranch($location = null)
    {
        if (is_string($location)) {
            return $this->fetch('/locations/branches/findbyaddress', 'GET', [
                'query' => [
                    'address'  => $location
                ]
            ]);
        }

        if (is_array($location)) {
            return $this->fetch('/locations/branches/coordinates', 'GET', [
                'query' => [
                    'latitude'  => $location[0],
                    'longitude'    => $location[1]
                ]
            ]);
        }
    }

    public function getNearestATM($latitude = 0, $longitude = 0)
    {
        return $this->fetch('/locations/atms/coordinates', 'GET', [
            'query' => [
                'latitude'  => $latitude,
                'longitude'    => $longitude
            ]
        ]);
    }
}
