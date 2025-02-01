<?php

use Behat\Behat\Context\Context;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class FeatureContext implements Context
{
    private $client;
    private $response;

    public function __construct()
    {
        $this->client = HttpClient::create();
    }

    /**
     * @Given I request :endpoint
     */
    public function iRequest($endpoint)
    {
        try {
            $this->response = $this->client->request('GET', 'http://127.0.0.1:8000/' . $endpoint);
        } catch (TransportExceptionInterface $e) {
            throw new \Exception("HTTP request failed: " . $e->getMessage());
        }
    }

    /**
     * @Then I should receive a successful response
     */
    public function iShouldReceiveASuccessfulResponse()
    {
        $statusCode = $this->response->getStatusCode();
        if ($statusCode !== 200) {
            throw new \Exception("Unexpected status code: $statusCode");
        }
    }

    /**
     * @Then I should receive a :statusCode response with error :errorMessage
     */
    public function iShouldReceiveAResponseWithError($statusCode, $errorMessage)
    {
        $actualStatusCode = $this->response->getStatusCode();
        $content = $this->response->getContent(false);
        $decoded = json_decode($content, true);

        if ($actualStatusCode != $statusCode) {
            throw new \Exception("Expected status code $statusCode but got $actualStatusCode. Response: " . $content);
        }

        if (!isset($decoded['error']) || $decoded['error'] !== $errorMessage) {
            throw new \Exception("Expected error message '$errorMessage' but got " . json_encode($decoded));
        }
    }

    /**
     * @Then I should see :text
     */
    public function iShouldSee($text)
    {
        $content = $this->response->getContent();
        if (strpos($content, $text) === false) {
            throw new \Exception("Text '$text' not found in response: " . $content);
        }
    }

    /**
     * @Then I should see at least one property
     */
    public function iShouldSeeAtLeastOneProperty()
    {
        $content = $this->response->getContent();
        $decoded = json_decode($content, true);

        if (!isset($decoded['data']) || count($decoded['data']) === 0) {
            throw new \Exception("Expected at least one property but got: " . $content);
        }
    }

    /**
     * @Then I should receive an empty data response
     */
    public function iShouldReceiveAnEmptyDataResponse()
    {
        $content = $this->response->getContent();
        $decoded = json_decode($content, true);

        if (!isset($decoded['data']) || count($decoded['data']) !== 0) {
            throw new \Exception("Expected empty 'data' but got: " . json_encode($decoded));
        }
    }

    /**
     * @Then I should see "meta" in response
     */
    public function iShouldSeeMetaInResponse()
    {
        $content = $this->response->getContent();
        $decoded = json_decode($content, true);

        if (!isset($decoded['meta'])) {
            throw new \Exception("Expected 'meta' field but response is: " . json_encode($decoded));
        }
    }
}
