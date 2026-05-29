<?php

return [
    /*
    |--------------------------------------------------------------------------
    | API Key
    |--------------------------------------------------------------------------
    |
    | Your Fawaterak API key. Required for authenticating all API requests.
    | This key will be sent as a Bearer token in every request.
    |
    */
    'api_key' => env('FAWATERK_API_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Mode
    |--------------------------------------------------------------------------
    |
    | Determines which API environment to use. Accepted values:
    |   - "staging"    → uses staging_url (for testing)
    |   - "production" → uses production_url (for live traffic)
    |
    */
    'mode' => env('FAWATERK_MODE', 'staging'),

    /*
    |--------------------------------------------------------------------------
    | Staging URL
    |--------------------------------------------------------------------------
    |
    | The base URL used when mode is set to "staging".
    |
    */
    'staging_url' => env('FAWATERK_STAGING_URL', 'https://staging.fawaterk.com/api/v2'),

    /*
    |--------------------------------------------------------------------------
    | Production URL
    |--------------------------------------------------------------------------
    |
    | The base URL used when mode is set to "production".
    |
    */
    'production_url' => env('FAWATERK_PRODUCTION_URL', 'https://app.fawaterk.com/api/v2'),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | Maximum number of seconds to wait for an API response before timing out.
    |
    */
    'timeout' => (int) env('FAWATERK_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Retry Attempts
    |--------------------------------------------------------------------------
    |
    | Number of times to retry a failed request before throwing an exception.
    | Set to 0 to disable retries.
    |
    */
    'retries' => (int) env('FAWATERK_RETRIES', 1),

    /*
    |--------------------------------------------------------------------------
    | Retry Delay (ms)
    |--------------------------------------------------------------------------
    |
    | Milliseconds to wait between retry attempts.
    |
    */
    'retry_delay' => (int) env('FAWATERK_RETRY_DELAY', 500),
];
