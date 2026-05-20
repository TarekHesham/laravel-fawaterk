# Laravel Fawaterk

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ElFarmawy/laravel-fawaterk.svg?style=flat-square)](https://packagist.org/packages/ElFarmawy/laravel-fawaterk)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/TarekHesham/laravel-fawaterk/run-tests.yml?branch=main)](https://github.com/TarekHesham/laravel-fawaterk/actions?query=workflow%3Arun-tests+branch%3Amain)

A lightweight Laravel API wrapper for the Fawaterk payment gateway.

## Package Overview

This package simplifies Fawaterk API integration in Laravel applications by providing clean, minimal abstractions. It is designed to be a thin wrapper around the official Fawaterk API, ensuring developers can interact with the payment gateway using structured data objects while maintaining full control.

* **Supported Features:** Invoice creation, status retrieval, payment initialization (redirect, Fawry, Meeza), tokenization support, and webhook processing.
* **Compatibility:** Laravel 10, 11, 12, 13; PHP 8.2+.

## Features

- **Invoice Management:** Create invoices, retrieve data, and verify payment status.
- **Payment Gateway:** Support for multiple payment methods including redirect, Fawry, and Meeza.
- **Tokenization:** Manage card tokenization, including creating screens and deleting tokens.
- **Webhook Support:** Secure signature verification and automated DTO parsing for webhook events.
- **Data Transfer Objects:** Strict typing with DTOs for requests and responses.

## Installation

Install the package via Composer:

```bash
composer require elfarmawy/laravel-fawaterk
```

The package will automatically register its service provider.

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag="fawaterk-config"
```

Update your `.env` file with your Fawaterk credentials:

```env
FAWATERK_API_KEY=your_api_key_here
FAWATERK_MODE=sandbox # or production
```

Configuration example (`config/fawaterk.php`):

```php
return [
    'api_key' => env('FAWATERK_API_KEY'),
    'mode' => env('FAWATERK_MODE', 'sandbox'),
    'base_url' => env('FAWATERK_MODE') === 'production' 
        ? 'https://api.fawaterk.com/api/v2' 
        : 'https://staging.fawaterk.com/api/v2',
];
```

## Usage

### Initializing the Client

The package provides a facade for easy access:

```php
use ElFarmawy\Fawaterk\Facades\Fawaterk;

// Creating an invoice link
$invoice = Fawaterk::invoices()->createInvoiceLink($requestData);
```

### Creating Invoices

```php
use ElFarmawy\Fawaterk\Data\CreateInvoiceRequest;

$request = new CreateInvoiceRequest([
    'cartItems' => [...],
    'customer' => [...],
    // ...
]);

$response = Fawaterk::invoices()->createInvoiceLink($request);
```

### Webhook Verification

```php
use ElFarmawy\Fawaterk\Services\FawaterkWebhookService;

$service = app(FawaterkWebhookService::class);
$payload = $request->all();
$signature = $request->header('X-Fawaterk-Signature');

if ($service->verifySignature($payload, $signature)) {
    // Process webhook
}
```

## Error Handling

The package throws specific exceptions to handle API failures:
- `ElFarmawy\Fawaterk\Exceptions\ApiException`: General API error.
- `ElFarmawy\Fawaterk\Exceptions\RequestException`: Validation or processing error.
- `ElFarmawy\Fawaterk\Exceptions\WebhookSignatureVerificationException`: Invalid webhook signature.

## Architecture Notes

- **DTO Philosophy:** The package uses Data Transfer Objects for all requests and responses to ensure type safety and facilitate IDE autocompletion.
- **Thin Abstraction:** We intentionally avoid complex business logic, acting purely as an API gateway.
- **Documentation Compliance:** Only official endpoints are supported.

## Security

Ensure your webhook endpoint validates the signature provided by Fawaterk to prevent unauthorized access. Always keep your API keys in secure environment variables.

## Changelog

Please see the [CHANGELOG.md](CHANGELOG.md) for more information on what has changed recently.

## License

The MIT License (MIT). Please see the [LICENSE](LICENSE) file for more information.
