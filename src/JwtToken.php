<?php

namespace E2Consult\DNBApiClient;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Aws\Signature\SignatureV4;
use Aws\Credentials\Credentials;
use Illuminate\Support\Facades\Cache;

class JwtToken
{
    private $api_key;
    private $credentials;

    protected $cache_key;
    protected $customerId;

    protected $token;
    protected $signer;
    protected $duration = 600;

    public function __construct(Credentials $credentials, $api_key, $customerId)
    {
        $this->credentials = $credentials;
        $this->api_key = $api_key;
        $this->customerId = $customerId;

        $this->signer = new SignatureV4(
            config('services.dnb.servcice') ?? 'execute-api',
            config('services.dnb.region') ?? 'eu-west-1'
        );

        $this->cache_key = 'services.dnb.token.'.$this->customerId;
        $this->token = null;
    }


    public function getTokenKey()
    {
        return $this->cache_key;
    }

    public function getJwtToken()
    {
        if (is_null($this->token)) {
            if (is_null($this->token = Cache::get($this->cache_key))) {
                $this->token = $this->getFreshToken(true);
            }
        }
        return $this->token->jwtToken;
    }

    public function getFreshToken($cache = true)
    {
        $signedrequest = $this->signer->signRequest(
            new Request('GET', config('services.dnb.endpoint') . '/token?customerId=' . json_encode([
                    'type' => 'SSN',
                    'value' => $this->customerId
                ]), [
                    'Accept'        => 'application/json',
                    'Content-Type'  => 'application/json',
                    'x-api-key'     => $this->api_key
                ]
            ),
            $this->credentials
        );
        $response = (new Client)->send($signedrequest);
        return tap(collect(json_decode($response->getBody())->tokenInfo)->first(), function ($token) use ($cache) {
            if ($cache) {
                Cache::put($this->cache_key, $token, $this->duration);
            }
        });
    }
}
