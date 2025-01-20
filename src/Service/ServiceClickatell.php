<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ServiceClickatell
{
    private HttpClientInterface $httpClient;
    private string $apiID;
    private string $apiKey;
    private string $apiUser;
    private string $apiPassword;
    private string $baseUrl;

    public function __construct(HttpClientInterface $httpClient, string $baseUrl, string $apiID, string $apiUser, string $apiPassword, string $apiKey)
    {
        $this->httpClient = $httpClient;
        $this->baseUrl = $baseUrl;
        $this->apiID = $apiID;
        $this->apiUser = $apiUser;
        $this->apiPassword = $apiPassword;
        $this->apiKey = $apiKey;
    }

    /**
     * Sends an SMS message via the Clickatell API.
     *
     * @param array $to The recipient's phone number (in international format).
     * @param string $message The message content.
     * @param bool $binary
     * @return array The API response as an associative array.
     *
     * @throws TransportExceptionInterface
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function sendBulkMessage(array $to, string $message, bool $binary = false): array
    {
        $url = $this->baseUrl . '/message';

        $response = $this->httpClient->request('POST', $url, [
            'headers' => [
                'X-Version' => 1,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'bearer ' . $this->apiKey,
            ],
            'json' => [
                'text' => $message,
                'to' => $to
            ],
        ]);

        dump($response);
        dump($response->toArray());

        return $response->toArray();
    }
}
