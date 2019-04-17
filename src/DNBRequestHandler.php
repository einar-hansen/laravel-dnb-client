<?php

namespace E2Consult\DNBApiClient;

use Aws\Signature\SignatureV4;
use Aws\Credentials\Credentials;
use Psr\Http\Message\RequestInterface;

class DNBRequestHandler
{
    private $api_key;
    private $customerId;
    private $credentials;

    protected $signer;
    protected $token;


    public function __construct(Credentials $credentials, $api_key, $customerId)
    {
        $this->api_key = $api_key;
        $this->customerId = $customerId;
        $this->credentials = $credentials;

        $this->signer = new SignatureV4(
            config('services.dnb.servcice') ?? 'execute-api',
            config('services.dnb.region') ?? 'eu-west-1'
        );
        $this->token = new JwtToken($this->credentials, $this->api_key, $this->customerId);
    }

    public function __invoke(callable $handler)
    {
        return function (RequestInterface $request, array $options) use ($handler) {
            return $handler(
                $this->signer->signRequest(
                    $request->withAddedHeader('Accept', 'application/json')
                        ->withAddedHeader('Content-Type', 'application/json')
                        ->withAddedHeader('x-api-key', $this->api_key)
                        ->withAddedHeader('x-dnbapi-jwt', $this->token->getJwtToken()),
                    $this->credentials
                ), $options);
        };
    }
}
