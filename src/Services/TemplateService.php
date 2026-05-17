<?php

namespace ElFarmawy\Template\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Config;
use ElFarmawy\Template\Contracts\TemplateInterface;
use ElFarmawy\Template\Exceptions\TemplateException;

class TemplateService implements TemplateInterface
{
    /**
     * @var string
     */
    protected string $apiKey;

    /**
     * @var string
     */
    protected string $baseUrl;

    /**
     * Create a new TemplateService instance.
     *
     * @param  array  $config  Optional configuration values (api_key, base_url)
     * @return void
     */
    public function __construct(array $config = [])
    {
        $this->apiKey  = $config['api_key']  ?? Config::get('template.api_key', '');
        $this->baseUrl = $config['base_url'] ?? Config::get('template.base_url', 'https://example.com/api');
    }

    /**
     * Set the API key.
     *
     * @param  string  $apiKey  The API key
     * @return void
     */
    public function setApiKey(string $apiKey): void
    {
        $this->apiKey = $apiKey;
    }

    /**
     * Set the base URL for API requests.
     *
     * @param  string  $baseUrl  The base URL
     * @return void
     */
    public function setBaseUrl(string $baseUrl): void
    {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    /**
     * Handle and validate an API response.
     *
     * Throws a TemplateException if the response status indicates an error
     * or if the response body is not valid JSON.
     *
     * @param  \Illuminate\Http\Client\Response  $response
     * @return array  The decoded JSON response
     *
     * @throws \ElFarmawy\Template\Exceptions\TemplateException
     */
    protected function handleResponse(Response $response): array
    {
        if (! $response->successful()) {
            $message = $response->json('message') ?? $response->body();
            throw new TemplateException("Template API error ({$response->status()}): {$message}");
        }

        $data = $response->json();

        if (! is_array($data)) {
            $data = json_decode($response->body(), true);
            if (! is_array($data)) {
                throw new TemplateException("Template API response is not an array.");
            }
        }

        return $data;
    }
}
