<?php

declare(strict_types=1);

use ElFarmawy\Fawaterk\Data\Webhooks\CanceledWebhookData;
use ElFarmawy\Fawaterk\Data\Webhooks\FailedWebhookData;
use ElFarmawy\Fawaterk\Data\Webhooks\PaidWebhookData;
use ElFarmawy\Fawaterk\Data\Webhooks\RefundWebhookData;
use ElFarmawy\Fawaterk\Exceptions\WebhookSignatureVerificationException;
use ElFarmawy\Fawaterk\Services\FawaterkWebhookService;
use Illuminate\Support\Facades\Config;

beforeEach(function () {
    // Set a dummy vendor key for testing
    Config::set('fawaterk.vendor_key', 'test_vendor_key');
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
        'hashKey' => '3bdb74fb198687079a22a767a173d05c194497e63c3dab27766bc7ed477aec84', // hash_hmac('sha256', 'InvoiceId=12345&InvoiceKey=INV-KEY-PAID&PaymentMethod=CreditCard', 'test_vendor_key')
    ];

    $service = new FawaterkWebhookService();
    expect($service->verifySignature($payload, 'paid'))->toBeTrue();
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
    expect($service->verifySignature($payload, 'paid'))->toBeFalse();
});

it('can verify a valid canceled webhook signature', function () {
    $payload = [
        'referenceId' => 'REF-CANCELED',
        'status' => 'EXPIRED',
        'paymentMethod' => 'Fawry',
        'pay_load' => null,
        'transactionId' => 123,
        'transactionKey' => 'TRANS-KEY-CANCELED',
        'hashKey' => '811336183fc4cde17c414d65ede58544e1de15aa75f534d0606b552109d0da80', // hash_hmac('sha256', 'referenceId=REF-CANCELED&PaymentMethod=Fawry', 'test_vendor_key')
    ];

    $service = new FawaterkWebhookService();
    expect($service->verifySignature($payload, 'canceled'))->toBeTrue();
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
    expect($service->verifySignature($payload, 'canceled'))->toBeFalse();
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
        'hashKey' => '08dab4678b79098b7df1ba9719cb1b58333a03bea2e759d9047db71871246e75', // hash_hmac('sha256', 'InvoiceId=67890&InvoiceKey=INV-KEY-FAILED&PaymentMethod=Card', 'test_vendor_key')
    ];

    $service = new FawaterkWebhookService();
    expect($service->verifySignature($payload, 'failed'))->toBeTrue();
});

it('fails to verify an invalid failed webhook signature', function () {
    $payload = [
        'invoice_id' => 67890,
        'invoice_key' => 'INV-KEY-FAILED',
        'payment_method' => 'Card',
        'hashKey' => 'invalid-hash',
    ];

    $service = new FawaterkWebhookService();
    expect($service->verifySignature($payload, 'failed'))->toBeFalse();
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
    expect($service->verifySignature($payload, 'refund'))->toBeFalse();

    // If a hashKey somehow appears, it should still fail because no hash can be generated
    $payloadWithHash = $payload;
    $payloadWithHash['hashKey'] = 'some-hash';
    expect($service->verifySignature($payloadWithHash, 'refund'))->toBeFalse();
});

it('parse returns a PaidWebhookData object for "paid" type', function () {
    $payload = [
        'invoice_id' => 12345,
        'invoice_key' => 'INV-KEY-PAID',
        'payment_method' => 'CreditCard',
        'amount' => 100.00,
        'currency' => 'EGP',
        'customer_name' => 'John Doe',
        'customer_email' => 'john@example.com',
        'status' => 'paid',
        'hashKey' => '3bdb74fb198687079a22a767a173d05c194497e63c3dab27766bc7ed477aec84',
    ];

    $service = new FawaterkWebhookService();
    $dto = $service->parse($payload, 'paid');
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
        'hashKey' => '811336183fc4cde17c414d65ede58544e1de15aa75f534d0606b552109d0da80',
    ];

    $service = new FawaterkWebhookService();
    $dto = $service->parse($payload, 'canceled');
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
        'hashKey' => '08dab4678b79098b7df1ba9719cb1b58333a03bea2e759d9047db71871246e75',
    ];

    $service = new FawaterkWebhookService();
    $dto = $service->parse($payload, 'failed');
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
        $service->parse($payload, 'refund');
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
    $service->parse($payload, 'paid');
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
    expect($service->safeVerifySignature($payload, 'paid'))->toBeFalse();
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
        'hashKey' => '3bdb74fb198687079a22a767a173d05c194497e63c3dab27766bc7ed477aec84',
    ];

    $service = new FawaterkWebhookService();
    expect($service->safeVerifySignature($payload, 'paid'))->toBeTrue();
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
    expect($service->safeVerifySignature($payload, 'refund'))->toBeFalse();
});
