# Laravel Fawaterk

<p align="center">
    <img src="https://banners.beyondco.de/Laravel%20Fawaterk.png?theme=light&packageManager=composer+require&packageName=elfarmawy%2Flaravel-fawaterk&pattern=architect&style=style_1&description=Modern+Laravel+SDK+for+the+Fawaterk+payment+gateway&md=1&showWatermark=0&fontSize=90px&images=credit-card" alt="Laravel Fawaterk" width="80%">
</p>
<p align="center">
<a href="https://packagist.org/packages/elfarmawy/laravel-fawaterk"><img src="https://img.shields.io/packagist/v/elfarmawy/laravel-fawaterk.svg?style=flat-square" alt="Latest Version on Packagist"></a>
<a href="https://packagist.org/packages/elfarmawy/laravel-fawaterk"><img src="https://img.shields.io/packagist/dt/elfarmawy/laravel-fawaterk.svg?style=flat-square" alt="Total Downloads"></a>
<a href="https://github.com/TarekHesham/laravel-fawaterk/actions"><img src="https://img.shields.io/github/actions/workflow/status/TarekHesham/laravel-fawaterk/run-tests.yml?branch=main&style=flat-square" alt="Tests"></a>
<a href="https://packagist.org/packages/elfarmawy/laravel-fawaterk"><img src="https://img.shields.io/packagist/php-v/elfarmawy/laravel-fawaterk?style=flat-square" alt="PHP Version"></a>
<a href="LICENSE.md"><img src="https://img.shields.io/packagist/l/elfarmawy/laravel-fawaterk?style=flat-square" alt="Software License"></a>
</p>

<p align="center">
    <strong>A modern, strongly-typed Laravel SDK for the Fawaterk payment gateway.</strong>
</p>

<p align="center">
Built with DTOs, Enums, Fluent Builders, Tokenization Support, Secure Webhooks, and a developer-first architecture.
</p>

---

# Introduction

Laravel Fawaterk is a modern Laravel SDK designed to simplify integration with the Fawaterk payment gateway while maintaining a clean and strongly-typed developer experience.

Unlike traditional payment wrappers that rely heavily on arrays and undocumented payloads, this package provides:

- Strongly-typed DTOs
- Immutable request & response objects
- Enum-driven APIs
- Fluent builders
- IDE-friendly autocompletion
- Secure webhook verification
- Laravel-native architecture
- SOLID & DDD-inspired design

The package is intentionally designed to feel natural inside Laravel applications while remaining framework-friendly and easy to extend.

---

# Features

- ✅ Invoice creation and payment verification
- ✅ Seamless integration for Cards, Installments, Mobile Wallets, Meeza, Fawry, Aman, and Basata.
- ✅ Payment methods retrieval
- ✅ Card tokenization support
- ✅ Typed DTO request & response objects
- ✅ Fluent request builders
- ✅ Secure HMAC webhook verification
- ✅ Immutable readonly objects
- ✅ Clean architecture
- ✅ Laravel Facade support
- ✅ PHPStan-friendly
- ✅ Pest testing support
- ✅ Laravel 10, 11, and 12 support

---

# Table of Contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Quick Start](#quick-start)
- [Invoices](#invoices)
  - [Create Invoice](#create-invoice)
  - [Get Invoice Data](#get-invoice-data)
  - [Verify Paid Invoice](#verify-paid-invoice)

- [Gateway Operations](#gateway-operations)
  - [Get Payment Methods](#get-payment-methods)
  - [Initialize Payment](#initialize-payment)

- [Tokenization](#tokenization)
  - [Create Token Screen](#create-token-screen)
  - [Pay With Token](#pay-with-token)
  - [Delete Token](#delete-token)

- [Webhooks](#webhooks)
- [Error Handling](#error-handling)
- [Testing](#testing)
- [Architecture](#architecture)
- [Security](#security)
- [Contributing](#contributing)
- [Changelog](#changelog)
- [License](#license)

---

# Requirements

| Dependency | Version          |
| ---------- | ---------------- |
| PHP        | 8.2+             |
| Laravel    | 10.x, 11.x, 12.x |

---

# Installation

You can install the package via Composer:

```bash id="n2n3qe"
composer require elfarmawy/laravel-fawaterk
```

Laravel will automatically register the service provider and facade.

---

# Configuration

Publish the configuration file:

```bash id="o1d0gh"
php artisan vendor:publish --tag="fawaterk-config"
```

Add your credentials to the `.env` file:

```env id="sdlf5g"
FAWATERK_API_KEY=your_api_key_here

FAWATERK_VENDOR_KEY=your_vendor_key_here

FAWATERK_MODE=sandbox
```

Available modes:

- `sandbox`
- `production`

---

# Quick Start

Create an invoice in seconds using the fluent builder API.

```php id="3db0l8"
use ElFarmawy\Fawaterk\Facades\Fawaterk;
use ElFarmawy\Fawaterk\Enums\Currency;
use ElFarmawy\Fawaterk\Data\Invoices\Requests\CreateInvoiceRequest;

$response = Fawaterk::invoices()->createInvoiceLink(
    CreateInvoiceRequest::builder()
        ->cartTotal(150)
        ->currency(Currency::EGP)
        ->customer($customer)
        ->cartItems($items)
        ->build()
);

return redirect($response->data->invoiceLink);
```

---

# Invoices

## Create Invoice

```php id="x7epn6"
use ElFarmawy\Fawaterk\Facades\Fawaterk;
use ElFarmawy\Fawaterk\Data\Invoices\Requests\CreateInvoiceRequest;
use ElFarmawy\Fawaterk\Data\Invoices\Shared\CustomerData;
use ElFarmawy\Fawaterk\Data\Invoices\Shared\CartItemData;
use ElFarmawy\Fawaterk\Enums\Currency;

$request = CreateInvoiceRequest::builder()
    ->cartTotal(150.50)
    ->currency(Currency::EGP)
    ->customer(
        new CustomerData(
            first_name: 'John',
            last_name: 'Doe',
            email: 'john@example.com',
            phone: '01000000000',
            address: '123 Main St'
        )
    )
    ->cartItems([
        new CartItemData(
            name: 'Product 1',
            price: 100,
            quantity: 1
        ),

        new CartItemData(
            name: 'Product 2',
            price: 50.5,
            quantity: 1
        ),
    ])
    ->build();

$response = Fawaterk::invoices()
    ->createInvoiceLink($request);

$invoiceId = $response->data->invoiceId;

$paymentLink = $response->data->invoiceLink;
```

---

## Get Invoice Data

```php id="4rlhwp"
$response = Fawaterk::invoices()
    ->getInvoiceData($invoiceId);
```

---

## Verify Paid Invoice

```php id="4x17om"
$verifiedInvoice = Fawaterk::invoices()
    ->verifyPaidInvoice($invoiceId);
```

This method throws a `RequestException` if the invoice is not paid or does not exist.

---

# Gateway Operations

## Get Payment Methods

```php id="f6f9e9"
$methods = Fawaterk::gateway()
    ->getPaymentMethods();

foreach ($methods as $method) {
    echo $method->name_en;
}
```

---

## Initialize Payment

```php id="dvw9my"
use ElFarmawy\Fawaterk\Data\Gateway\Requests\InitPayRequest;
use ElFarmawy\Fawaterk\Enums\Currency;

$request = new InitPayRequest(
    payment_method_id: 2,
    cartTotal: 100,
    currency: Currency::EGP,
    customer: $customerData,
    cartItems: $cartItems
);

$response = Fawaterk::gateway()
    ->invoiceInitPay($request);
```

Depending on the selected payment method, the response will automatically resolve into one of the following DTOs:

- `InitPayRedirectResponse`
- `InitPayFawryResponse`
- `InitPayMeezaResponse`

---

# Tokenization

## Create Token Screen

Generate a secure hosted screen for saving customer cards.

```php id="jlwmf7"
use ElFarmawy\Fawaterk\Data\Tokenization\Requests\CreateTokenScreenRequest;
use ElFarmawy\Fawaterk\Data\Shared\RedirectionUrlsData;

$request = new CreateTokenScreenRequest(
    customer: $customerData,

    redirectionUrls: new RedirectionUrlsData(
        successUrl: 'https://example.com/success',
        failUrl: 'https://example.com/fail',
        pendingUrl: 'https://example.com/pending'
    )
);

$response = Fawaterk::gateway()
    ->createCardTokenScreen($request);

$url = $response->url;
```

---

## Pay With Token

```php id="qih13x"
use ElFarmawy\Fawaterk\Data\Tokenization\Requests\PayWithTokenRequest;

$request = new PayWithTokenRequest(
    token: 'customer_saved_token',
    cartTotal: 200,
    currency: Currency::EGP,
    customer: $customerData,
    cartItems: $cartItems
);

$response = Fawaterk::gateway()
    ->payWithToken($request);
```

---

## Delete Token

```php id="wz7t5n"
$response = Fawaterk::gateway()
    ->deleteToken($token);
```

---

# Webhooks

Laravel Fawaterk provides secure webhook verification and automatic payload parsing into typed DTOs.

Supported webhook events:

- `paid`
- `failed`
- `canceled`
- `refund`

---

## Example Controller

```php id="gmy7kn"
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use ElFarmawy\Fawaterk\Services\FawaterkWebhookService;
use ElFarmawy\Fawaterk\Data\Webhooks\PaidWebhookData;
use ElFarmawy\Fawaterk\Exceptions\WebhookSignatureVerificationException;

class FawaterkWebhookController extends Controller
{
    public function __invoke(
        Request $request,
        FawaterkWebhookService $webhookService
    ) {
        $payload = $request->all();

        $webhookType = $request->query('type');

        try {

            $webhook = $webhookService->parse(
                payload: $payload,
                webhookType: $webhookType
            );

            if ($webhook instanceof PaidWebhookData) {

                // Handle successful payment

            }

            return response()->json([
                'status' => 'success',
            ]);

        } catch (WebhookSignatureVerificationException $exception) {

            abort(403, 'Invalid webhook signature.');

        }
    }
}
```

---

## Verify Signature Only

```php id="srm8ml"
$isValid = $webhookService->verifySignature(
    payload: $payload,
    signature: $signature
);
```

---

# Error Handling

Laravel Fawaterk maps API responses into typed exceptions.

```php id="jvdv4h"
use ElFarmawy\Fawaterk\Exceptions\ApiException;
use ElFarmawy\Fawaterk\Exceptions\RequestException;
use ElFarmawy\Fawaterk\Exceptions\ValidationException;
use ElFarmawy\Fawaterk\Exceptions\AuthenticationException;

try {

    $response = Fawaterk::invoices()
        ->createInvoiceLink($request);

} catch (ValidationException $exception) {

    $errors = $exception->errors;

} catch (AuthenticationException $exception) {

    // Invalid API credentials

} catch (RequestException $exception) {

    // Request processing error

} catch (ApiException $exception) {

    // Generic API exception

}
```

---

# Testing

Run the test suite:

```bash id="4w3e8o"
composer test
```

or

```bash id="zjlwmh"
vendor/bin/pest
```

---

# Architecture

Laravel Fawaterk follows a layered architecture focused on maintainability, testability, and developer experience.

```text id="1lr03i"
Application
    ↓
Fawaterk Facade
    ↓
Services
    ↓
DTOs / Builders
    ↓
HTTP Client
    ↓
Fawaterk API
```

---

## Core Principles

### DTO-first Design

Every request and response is represented using strongly-typed DTOs instead of arrays.

### Immutable Objects

All DTOs are immutable and readonly where possible.

### Enum-driven APIs

Enums are used extensively to prevent invalid payload values.

### Thin Service Layer

The package avoids unnecessary business logic and acts as a predictable SDK around the official Fawaterk API.

### Laravel-native Experience

The SDK integrates naturally with Laravel conventions, facades, dependency injection, and the HTTP client.

---

# Security

If you discover any security vulnerabilities, please report them responsibly instead of opening a public issue.

---

# Contributing

Contributions are welcome and greatly appreciated.

Please submit a pull request or open an issue for discussion before large changes.

---

# Changelog

Please see [CHANGELOG.md](CHANGELOG.md) for more information about recent changes.

---

# License

The MIT License (MIT).

Please see [LICENSE.md](LICENSE.md) for more information.
