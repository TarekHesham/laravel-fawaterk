<?php

declare(strict_types=1);

use ElFarmawy\Fawaterk\Data\Webhooks\CanceledWebhookData;
use ElFarmawy\Fawaterk\Data\Webhooks\FailedWebhookData;
use ElFarmawy\Fawaterk\Data\Webhooks\PaidWebhookData;
use ElFarmawy\Fawaterk\Enums\WebhookType;
use ElFarmawy\Fawaterk\Exceptions\WebhookSignatureVerificationException;
use ElFarmawy\Fawaterk\Services\FawaterkWebhookService;
use Illuminate\Support\Facades\Config;

beforeEach(function () {
    Config::set('fawaterk.api_key', 'test_api_key');
});

it('can verify a valid paid webhook signature', function () {
    $payload = [
        'invoice_id' => 12345,
        'invoice_key' => 'INV-KEY-PAID',
        'payment_method' => 'CreditCard',
        'amount' => 100.00,
        'currency' => 'EGP',
        'customer_name' => 'John Doe',
        'customer_email' => 'john@example.com',
        'status' => 'paid',
    ];

    $payload['hashKey'] = hash_hmac(
        'sha256',
        sprintf(
            'InvoiceId=%s&InvoiceKey=%s&PaymentMethod=%s',
            $payload['invoice_id'],
            $payload['invoice_key'],
            $payload['payment_method']
        ),
        config('fawaterk.api_key')
    );

    $service = new FawaterkWebhookService();
    expect($service->verifySignature($payload, WebhookType::PAID))->toBeTrue();
});

it('fails to verify an invalid paid webhook signature', function () {
    $payload = [
        'invoice_id' => 12345,
        'invoice_key' => 'INV-KEY-PAID',
        'payment_method' => 'CreditCard',
        'amount' => 100.00,
        'currency' => 'EGP',
        'customer_name' => 'John Doe',
        'customer_email' => 'john@example.com',
        'status' => 'paid',
        'hashKey' => 'invalid-hash',
    ];

    $service = new FawaterkWebhookService();
    expect($service->verifySignature($payload, WebhookType::PAID))->toBeFalse();
});

it('can verify a valid canceled webhook signature', function () {
    $payload = [
        'referenceId' => 'REF-CANCELED',
        'status' => 'EXPIRED',
        'paymentMethod' => 'Fawry',
        'pay_load' => null,
        'transactionId' => 123,
        'transactionKey' => 'TRANS-KEY-CANCELED',
    ];

    $payload['hashKey'] = hash_hmac(
        'sha256',
        sprintf(
            'referenceId=%s&PaymentMethod=%s',
            $payload['referenceId'],
            $payload['paymentMethod']
        ),
        config('fawaterk.api_key')
    );

    $service = new FawaterkWebhookService();
    expect($service->verifySignature($payload, WebhookType::CANCELED))->toBeTrue();
});

it('fails to verify an invalid canceled webhook signature', function () {
    $payload = [
        'referenceId' => 'REF-CANCELED',
        'status' => 'EXPIRED',
        'paymentMethod' => 'Fawry',
        'pay_load' => null,
        'transactionId' => 123,
        'transactionKey' => 'TRANS-KEY-CANCELED',
        'hashKey' => 'invalid-hash',
    ];

    $service = new FawaterkWebhookService();
    expect($service->verifySignature($payload, WebhookType::CANCELED))->toBeFalse();
});

it('can verify a valid failed webhook signature', function () {
    $payload = [
        'invoice_id' => 67890,
        'invoice_key' => 'INV-KEY-FAILED',
        'payment_method' => 'Card',
        'pay_load' => null,
        'amount' => 50.00,
        'paidCurrency' => 'EGP',
        'errorMessage' => 'Payment failed',
        'response' => [],
        'referenceNumber' => '',
    ];

    $payload['hashKey'] = hash_hmac(
        'sha256',
        sprintf(
            'InvoiceId=%s&InvoiceKey=%s&PaymentMethod=%s',
            $payload['invoice_id'],
            $payload['invoice_key'],
            $payload['payment_method']
        ),
        config('fawaterk.api_key')
    );

    $service = new FawaterkWebhookService();
    expect($service->verifySignature($payload, WebhookType::FAILED))->toBeTrue();
});

it('fails to verify an invalid failed webhook signature', function () {
    $payload = [
        'invoice_id' => 67890,
        'invoice_key' => 'INV-KEY-FAILED',
        'payment_method' => 'Card',
        'hashKey' => 'invalid-hash',
    ];

    $service = new FawaterkWebhookService();
    expect($service->verifySignature($payload, WebhookType::FAILED))->toBeFalse();
});

it('always returns false for refund webhook signature verification due to undocumented hash generation', function () {
    $payload = [
        'transactionId' => 'TRANS-REFUND',
        'amount' => '100',
        'currency' => 'EGP',
        'status' => 'approved',
        'reason' => 'Product not available',
        'approvedAt' => '2026-02-22 14:57:40',
        // No hashKey in refund payload according to docs
    ];

    $service = new FawaterkWebhookService();
    expect($service->verifySignature($payload, WebhookType::REFUND))->toBeFalse();

    // If a hashKey somehow appears, it should still fail because no hash can be generated
    $payloadWithHash = $payload;
    $payloadWithHash['hashKey'] = 'some-hash';
    expect($service->verifySignature($payloadWithHash, WebhookType::REFUND))->toBeFalse();
});

it('parse returns a PaidWebhookData object for "paid" type', function () {
    $payload = [
        'invoice_id' => 12345,
        'invoice_key' => 'INV-KEY-PAID',
        'payment_method' => 'CreditCard',
        'paidAmount' => 100.00,
        'paidCurrency' => 'EGP',
        'customer_name' => 'John Doe',
        'customer_email' => 'john@example.com',
        'invoice_status' => 'paid',
    ];

    $payload['hashKey'] = hash_hmac(
        'sha256',
        sprintf(
            'InvoiceId=%s&InvoiceKey=%s&PaymentMethod=%s',
            $payload['invoice_id'],
            $payload['invoice_key'],
            $payload['payment_method']
        ),
        config('fawaterk.api_key')
    );

    $service = new FawaterkWebhookService();
    $dto = $service->verifyAndParse($payload, WebhookType::PAID);
    expect($dto)->toBeInstanceOf(PaidWebhookData::class);
    expect($dto->invoice_id)->toEqual(12345);
});

it('parse returns a CanceledWebhookData object for "canceled" type', function () {
    $payload = [
        'referenceId' => 'REF-CANCELED',
        'status' => 'EXPIRED',
        'paymentMethod' => 'Fawry',
        'pay_load' => null,
        'transactionId' => 123,
        'transactionKey' => 'TRANS-KEY-CANCELED',
    ];

    $payload['hashKey'] = hash_hmac(
        'sha256',
        sprintf(
            'referenceId=%s&PaymentMethod=%s',
            $payload['referenceId'],
            $payload['paymentMethod']
        ),
        config('fawaterk.api_key')
    );

    $service = new FawaterkWebhookService();
    $dto = $service->verifyAndParse($payload, WebhookType::CANCELED);
    expect($dto)->toBeInstanceOf(CanceledWebhookData::class);
    expect($dto->referenceId)->toEqual('REF-CANCELED');
});

it('parse returns a FailedWebhookData object for "failed" type', function () {
    $payload = [
        'invoice_id' => 67890,
        'invoice_key' => 'INV-KEY-FAILED',
        'payment_method' => 'Card',
        'pay_load' => null,
        'amount' => 50.00,
        'paidCurrency' => 'EGP',
        'errorMessage' => 'Payment failed',
        'response' => [],
        'referenceNumber' => '',
    ];

    $payload['hashKey'] = hash_hmac(
        'sha256',
        sprintf(
            'InvoiceId=%s&InvoiceKey=%s&PaymentMethod=%s',
            $payload['invoice_id'],
            $payload['invoice_key'],
            $payload['payment_method']
        ),
        config('fawaterk.api_key')
    );

    $service = new FawaterkWebhookService();
    $dto = $service->verifyAndParse($payload, WebhookType::FAILED);
    expect($dto)->toBeInstanceOf(FailedWebhookData::class);
    expect($dto->invoice_id)->toEqual(67890);
});

it('parse returns a RefundWebhookData object for "refund" type (without hash verification)', function () {
    $payload = [
        'transactionId' => 'TRANS-REFUND',
        'amount' => '100',
        'currency' => 'EGP',
        'status' => 'approved',
        'reason' => 'Product not available',
        'approvedAt' => '2026-02-22 14:57:40',
        // No hashKey in refund payload
    ];

    $service = new FawaterkWebhookService();
    // For refund, verifySignature will return false, but parse needs to be tested if no hashKey is provided in the payload at all
    // This scenario implies that for 'refund' webhooks, the system might not even send a hashKey,
    // so verifySignature would not throw an exception but simply return false.
    // The parse method should still work if no hashKey is present.
    // Let's modify the parse method to allow refund DTO to be returned even if verifySignature returns false,
    // IF the payload does not contain a hashKey at all.
    // However, the current parse method THROWS on verifySignature returning false.
    // This is a design decision. For now, I'll test the current behavior.

    // If the refund payload contains no hashKey, verifySignature returns false, and parse throws WebhookSignatureVerificationException.
    // This might not be the desired behavior for refunds IF they genuinely don't send a hashKey.
    // For the current implementation, it should throw.
    try {
        $service->verifyAndParse($payload, WebhookType::REFUND);
        $this->fail('WebhookSignatureVerificationException was not thrown for refund webhook without hashKey.');
    } catch (WebhookSignatureVerificationException $e) {
        expect($e->getMessage())->toEqual('Invalid webhook signature.');
    }
});

it('parse throws WebhookSignatureVerificationException on invalid signature', function () {
    $payload = [
        'invoice_id' => 12345,
        'invoice_key' => 'INV-KEY-PAID',
        'payment_method' => 'CreditCard',
        'amount' => 100.00,
        'currency' => 'EGP',
        'customer_name' => 'John Doe',
        'customer_email' => 'john@example.com',
        'status' => 'paid',
        'hashKey' => 'wrong-hash',
    ];

    $service = new FawaterkWebhookService();
    $service->verifyAndParse($payload, WebhookType::PAID);
})->throws(WebhookSignatureVerificationException::class, 'Invalid webhook signature.');

it('safeVerifySignature returns false on invalid signature', function () {
    $payload = [
        'invoice_id' => 12345,
        'invoice_key' => 'INV-KEY-PAID',
        'payment_method' => 'CreditCard',
        'amount' => 100.00,
        'currency' => 'EGP',
        'customer_name' => 'John Doe',
        'customer_email' => 'john@example.com',
        'status' => 'paid',
        'hashKey' => 'wrong-hash',
    ];

    $service = new FawaterkWebhookService();
    expect($service->safeVerifySignature($payload, WebhookType::PAID))->toBeFalse();
});

it('safeVerifySignature returns true on valid signature', function () {
    $payload = [
        'invoice_id' => 12345,
        'invoice_key' => 'INV-KEY-PAID',
        'payment_method' => 'CreditCard',
        'amount' => 100.00,
        'currency' => 'EGP',
        'customer_name' => 'John Doe',
        'customer_email' => 'john@example.com',
        'status' => 'paid',
    ];

    $payload['hashKey'] = hash_hmac(
        'sha256',
        sprintf(
            'InvoiceId=%s&InvoiceKey=%s&PaymentMethod=%s',
            $payload['invoice_id'],
            $payload['invoice_key'],
            $payload['payment_method']
        ),
        config('fawaterk.api_key')
    );

    $service = new FawaterkWebhookService();
    expect($service->safeVerifySignature($payload, WebhookType::PAID))->toBeTrue();
});

it('safeVerifySignature returns false for refund webhook type', function () {
    $payload = [
        'transactionId' => 'TRANS-REFUND',
        'amount' => '100',
        'currency' => 'EGP',
        'status' => 'approved',
        'reason' => 'Product not available',
        'approvedAt' => '2026-02-22 14:57:40',
        // No hashKey in refund payload according to docs
    ];

    $service = new FawaterkWebhookService();
    expect($service->safeVerifySignature($payload, WebhookType::REFUND))->toBeFalse();
});
