# Security Guidelines for Laravel Fawaterk

This document provides security best practices for using and maintaining the Laravel Fawaterk SDK.

## Table of Contents

- [API Key Management](#api-key-management)
- [Webhook Security](#webhook-security)
- [HTTPS/TLS Requirements](#httpstls-requirements)
- [Error Handling & Logging](#error-handling--logging)
- [Input Validation](#input-validation)
- [Dependency Management](#dependency-management)
- [Reporting Vulnerabilities](#reporting-vulnerabilities)

---

## API Key Management

### Storage

- **Never commit API keys** to version control. Use environment variables exclusively.
- Store `FAWATERK_API_KEY` in `.env` file (not committed to Git)
- Use `.env.example` with placeholder values for documentation

```bash
# .env (NOT committed)
FAWATERK_API_KEY=your_actual_api_key_here

# .env.example (committed to repository)
FAWATERK_API_KEY=your_api_key_here
```

### Key Rotation

- Rotate API keys **quarterly** or when team members leave
- Use separate API keys for staging and production environments
- Never share API keys via email, chat, or unencrypted channels
- Use Laravel's `env()` helper or configuration management for all secrets

### Configuration

```php
// config/fawaterk.php example
'api_key' => env('FAWATERK_API_KEY', ''), // Load from environment

// Use Laravel's key management in production
'api_key' => env('FAWATERK_API_KEY', config('app.key')),
```

---

## Webhook Security

### Signature Verification

**Always verify webhook signatures** before processing webhook data. This ensures webhooks originate from Fawaterk.

```php
use ElFarmawy\Fawaterk\Facades\Fawaterk;
use ElFarmawy\Fawaterk\Enums\WebhookType;

Route::post('/webhooks/fawaterk', function (Request $request) {
    $payload = $request->all();
    $webhookType = WebhookType::PAID; // or CANCELED, FAILED, REFUND

    try {
        // CRITICAL: Always verify the signature first
        $webhookData = Fawaterk::webhook()
            ->verifyAndParse($payload, $webhookType);

        // Process webhook data safely
        // ...
    } catch (WebhookSignatureVerificationException $e) {
        // Log and reject invalid webhooks
        Log::warning('Invalid webhook signature: ' . $e->getMessage());
        return response()->json(['error' => 'Invalid signature'], 403);
    }
});
```

### Webhook Endpoint Security

1. **Use HTTPS only** - Require SSL/TLS certificates
2. **Implement rate limiting** - Prevent abuse/DoS attacks
3. **Validate HTTP headers** - Check `Content-Type: application/json`
4. **Implement idempotency** - Handle duplicate webhooks gracefully
5. **Add IP whitelisting** - Restrict to Fawaterk's IP ranges (if available)

```php
// Middleware example: Rate limiting webhooks
Route::post('/webhooks/fawaterk', function (Request $request) {
    // ...
})->middleware(['throttle:100,1']); // 100 requests per minute

// Middleware example: Require HTTPS
Route::post('/webhooks/fawaterk', function (Request $request) {
    // ...
})->middleware(['https']);
```

### Supported Webhook Types

| Type     | Supported | Status                                          |
|----------|-----------|------------------------------------------------|
| PAID     | ✅        | Fully verified with HMAC-SHA256                |
| CANCELED | ✅        | Fully verified with HMAC-SHA256                |
| FAILED   | ✅        | Fully verified with HMAC-SHA256                |
| REFUND   | ⚠️        | **Verification not yet documented by Fawaterk** |

**For REFUND webhooks:** Contact Fawaterk support to confirm the hash generation algorithm before processing them in production.

---

## HTTPS/TLS Requirements

### Production Environment

- **Always use HTTPS** - The SDK enforces HTTPS in production mode
- **Verify SSL certificates** - Use properly signed certificates from trusted CAs
- **Use TLS 1.2 or higher** - Disable older SSL/TLS versions

```php
// .env for production
FAWATERK_MODE=production
FAWATERK_PRODUCTION_URL=https://app.fawaterk.com/api/v2 // HTTPS enforced
```

### Staging Environment

- Use HTTPS when possible for consistency
- HTTP is only acceptable for local development

```php
// .env for local development
FAWATERK_MODE=staging
FAWATERK_STAGING_URL=https://staging.fawaterk.com/api/v2
```

### URL Validation

The SDK validates all configured URLs to prevent SSRF attacks:
- ✅ URLs must be valid (format validation)
- ✅ Production URLs must use HTTPS
- ✅ Only official Fawaterk domains are allowed

```php
// These will throw InvalidArgumentException:
FAWATERK_PRODUCTION_URL=http://attacker.com/api  // HTTP in production
FAWATERK_STAGING_URL=ftp://staging.fawaterk.com  // Invalid protocol
FAWATERK_MODE_URL=https://fake-fawaterk.com      // Unauthorized domain
```

---

## Error Handling & Logging

### Sensitive Data Protection

**Never log sensitive data** including:
- Payment tokens
- Card numbers
- Customer personal information (name, email, phone)
- API responses containing transactional details
- API keys or secrets

### Safe Logging

```php
// ❌ WRONG - Exposes customer data and payment tokens
try {
    $invoice = Fawaterk::invoices()->create($request);
} catch (ApiException $e) {
    Log::error('Invoice creation failed', ['exception' => $e]); // Logs sensitive context
}

// ✅ CORRECT - Sanitizes sensitive data
try {
    $invoice = Fawaterk::invoices()->create($request);
} catch (ApiException $e) {
    Log::error('Invoice creation failed', [
        'code' => $e->getCode(),
        'message' => $e->getMessage(), // Safe generic error message
        // Don't log getContext() - it may contain sensitive data
    ]);
}
```

### Error Responses to Users

```php
// ❌ WRONG - Exposes API implementation details
return response()->json([
    'error' => $e->getMessage(), // API error details
    'context' => $e->getContext(), // Sensitive data
], 500);

// ✅ CORRECT - Generic user-friendly message
return response()->json([
    'error' => 'Payment processing failed. Please try again later.',
    'transaction_id' => $invoiceId, // Safe identifier only
], 500);
```

### Logging Configuration

```php
// config/logging.php - Filter sensitive fields
'channels' => [
    'stack' => [
        'driver' => 'stack',
        'channels' => ['single'],
        'ignore_exceptions' => false,
    ],
    'single' => [
        'driver' => 'single',
        'path' => storage_path('logs/laravel.log'),
        // Only log non-sensitive exceptions
    ],
],
```

---

## Input Validation

### Payment Amount Validation

The SDK automatically validates payment amounts:

```php
use ElFarmawy\Fawaterk\Data\Gateway\Requests\InitPayRequest;

// ❌ THROWS InvalidArgumentException
InitPayRequest(
    payment_method_id: 1,
    cartTotal: -100,  // Negative amounts rejected
    // ...
);

// ❌ THROWS InvalidArgumentException
InitPayRequest(
    payment_method_id: 1,
    cartTotal: 5000000,  // Exceeds 1,000,000 limit
    // ...
);

// ✅ CORRECT
InitPayRequest(
    payment_method_id: 1,
    cartTotal: 150.50,  // Valid amount
    // ...
);
```

### Email Validation

Emails are validated against RFC standards:

```php
// ❌ THROWS InvalidArgumentException
CustomerData(
    firstName: 'John',
    lastName: 'Doe',
    email: 'invalid-email', // Invalid format
);

// ✅ CORRECT
CustomerData(
    firstName: 'John',
    lastName: 'Doe',
    email: 'john@example.com', // Valid format
);
```

### Customer Name Validation

Customer names are validated for allowed characters:

```php
// ✅ ACCEPTED
CustomerData(
    firstName: 'John-Paul',      // Hyphens allowed
    lastName: 'O\'Neill',         // Apostrophes allowed
);

// ⚠️ LOGGED AS WARNING (but accepted)
CustomerData(
    firstName: 'Иван',           // Non-ASCII characters logged
);
```

---

## Dependency Management

### Keep Dependencies Updated

- Monitor Composer dependencies for security updates
- Use `composer audit` to check for known vulnerabilities

```bash
# Check for vulnerabilities
composer audit

# Update all dependencies (test before production)
composer update

# Update specific package
composer update illuminate/http
```

### Security-Related Dependencies

Monitor these critical packages for updates:
- `illuminate/http` - HTTP client and request handling
- `illuminate/support` - Laravel framework utilities

---

## Rate Limiting

### Client-Side Rate Limiting

Enable rate limiting to prevent API abuse:

```php
// .env
FAWATERK_RATE_LIMIT_ENABLED=true
FAWATERK_RATE_LIMIT_RPM=60  // 60 requests per minute
```

### Retry Strategy

The SDK uses exponential backoff with jitter to avoid thundering herd:

```php
// .env
FAWATERK_RETRIES=3           // Retry failed requests 3 times
FAWATERK_RETRY_DELAY=500     // Start with 500ms delay
// Actual delays: 500ms, 1000ms, 2000ms (with jitter)
```

### Server-Side Rate Limiting

Implement rate limiting on your webhook endpoints:

```php
// routes/api.php
Route::post('/webhooks/fawaterk', WebhookController::class)
    ->middleware('throttle:100,1'); // 100 requests per minute
```

---

## Compliance Considerations

### GDPR

- Do not log customer personal information
- Implement data retention policies
- Provide mechanisms to delete customer data
- Use the SDK's built-in PII protection (no direct PII logging)

### PCI-DSS

- Never store raw payment card data
- Use Fawaterk's tokenization endpoints for card data
- Use HTTPS for all API communications
- Validate webhook signatures
- Keep dependencies updated

### OWASP Top 10

| Category                    | Status | Notes                                          |
|-----------------------------|--------|------------------------------------------------|
| Broken Access Control       | ✅     | Webhook signature verification implemented    |
| Cryptographic Failures      | ✅     | HTTPS enforced in production                   |
| Injection                   | ✅     | Input validation on amounts and emails        |
| Insecure Design             | ✅     | Security-by-default with error handling        |
| Security Misconfiguration   | ✅     | Config validation on startup                   |
| Vulnerable Components       | ✅     | Monitor dependencies with `composer audit`    |
| Auth Failures               | ✅     | Bearer token authentication                    |
| Software/Data Integrity     | ✅     | HMAC webhook verification                      |
| Logging & Monitoring        | ✅     | Sensitive data sanitization                    |
| SSRF                        | ✅     | URL validation and hostname whitelisting       |

---

## Environment Variables

### Production

```bash
# REQUIRED
FAWATERK_API_KEY=sk_live_xxxxxxxxxxxxx
FAWATERK_MODE=production

# OPTIONAL (defaults provided)
FAWATERK_PRODUCTION_URL=https://app.fawaterk.com/api/v2
FAWATERK_TIMEOUT=30
FAWATERK_RETRIES=3
FAWATERK_RETRY_DELAY=500
FAWATERK_RATE_LIMIT_ENABLED=true
FAWATERK_RATE_LIMIT_RPM=60
```

### Staging

```bash
# REQUIRED
FAWATERK_API_KEY=sk_test_xxxxxxxxxxxxx
FAWATERK_MODE=staging

# OPTIONAL (defaults provided)
FAWATERK_STAGING_URL=https://staging.fawaterk.com/api/v2
FAWATERK_TIMEOUT=30
FAWATERK_RETRIES=1
FAWATERK_RETRY_DELAY=500
```

---

## Reporting Vulnerabilities

### How to Report

If you discover a security vulnerability, please report it responsibly:

1. **Email:** tarekelfarmawy@outlook.com
2. **Do not open a public GitHub issue** for security vulnerabilities
3. Include:
   - Description of the vulnerability
   - Steps to reproduce
   - Potential impact
   - Suggested fix (if available)

### Response Timeline

- **Initial response:** Within 48 hours
- **Fix delivery:** Within 7 days (critical), 30 days (high)
- **Public disclosure:** After fix is released and users have time to update

### Security Update Process

1. Security issue is reported privately
2. SDK maintainer reproduces and confirms the issue
3. Fix is developed and tested
4. Security advisory is prepared
5. Fix is released as a patch version
6. Users are notified via security advisory

---

## Checklist for Deployment

Before deploying Laravel Fawaterk to production:

- [ ] API keys are stored in environment variables (not in code)
- [ ] `FAWATERK_MODE=production` is set
- [ ] HTTPS is enforced on all API endpoints
- [ ] Webhook endpoints require HTTPS
- [ ] Rate limiting is implemented on webhook endpoints
- [ ] Webhook signatures are verified before processing
- [ ] Error logs don't contain sensitive data
- [ ] Dependencies are up-to-date (`composer audit`)
- [ ] SSL certificate is valid and not self-signed
- [ ] API key rotation policy is documented
- [ ] Team has been trained on security best practices
- [ ] Incident response plan is in place

---

## Additional Resources

- [Laravel Security Documentation](https://laravel.com/docs/security)
- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [GDPR Compliance Guide](https://gdpr-info.eu/)
- [PCI-DSS Requirements](https://www.pcisecuritystandards.org/)
- [Fawaterk Documentation](https://fawaterk.com/docs)

---

## Questions?

For security questions or clarifications, contact the maintainer:
- **Email:** tarekelfarmawy@outlook.com
- **GitHub:** https://github.com/TarekHesham/laravel-fawaterk

**Last Updated:** May 31, 2026
