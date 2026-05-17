<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Template API Key
    |--------------------------------------------------------------------------
    |
    | This value is the API key for your Template account. This key is
    | used to authenticate all API requests to the Template service.
    |
    */
    'api_key' => env('API_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Template API Base URL
    |--------------------------------------------------------------------------
    |
    | This is the base URL for the Template API. You shouldn't need to
    | change this value unless Template changes their API endpoint.
    |
    */
    'base_url' => env('BASE_URL', 'https://example.com/api'),
];
