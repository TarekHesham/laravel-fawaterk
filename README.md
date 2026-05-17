# Laravel API Wrapper Template

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ElFarmawy/laravel-api-wrapper-template.svg?style=flat-square)](https://packagist.org/packages/ElFarmawy/laravel-api-wrapper-template)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/TarekHesham/laravel-api-wrapper-template/run-tests.yml?branch=main)](https://github.com/TarekHesham/laravel-api-wrapper-template/actions?query=workflow%3Arun-tests+branch%3Amain)

A **starter template** for creating Laravel API Wrapper packages — providing a clean structure, config system, and testing setup.
This package contains **no prebuilt request methods**, giving you full freedom to implement your own integration logic. **It’s not meant to be installed directly in production projects**.

## Quick Start

### 1. Clone the template

```bash
git clone https://github.com/TarekHesham/laravel-api-wrapper-template.git your-api-wrapper
cd your-api-wrapper
```

### 2. Rename namespaces

Replace:

```
ElFarmawy\Template
```

with your own package namespace, e.g.:

```
ElFarmawy\Paymob
```

### 3. Update composer.json

Edit:

```json
"name": "elfarmawy/laravel-api-wrapper-template",
"description": "Template for building Laravel API wrappers."
```

to match your new package name and purpose.

### 4. Run tests

```bash
composer install
composer test
```

---

## What’s Included

- Ready-to-use Laravel service provider and facade setup
- Config publishing support
- Custom exception handling
- PHPUnit + Orchestra Testbench setup
- Clean class structure to extend and customize

---

## How to Extend

Once you rename the namespace, you can build your own API wrapper methods:

```php
namespace ElFarmawy\Paymob\Services;

use Illuminate\Support\Facades\Http;

class PaymobService extends TemplateService
{
    public function createPayment(array $payload)
    {
        $response = Http::withToken($this->apiKey)
            ->post($this->baseUrl.'/payment', $payload);

        return $this->handleResponse($response);
    }
}
```

And then register it via your own Facade and Service Provider.

---

## Changelog

See [CHANGELOG](CHANGELOG.md) for updates.

## Contributing

Pull requests are welcome! Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover a security issue, please email **[tarekelfarmawy@outlook.com](mailto:tarekelfarmawy@outlook.com)**.

## Credits

- [Tarek Hesham](https://github.com/TarekHesham)
- [All Contributors](../../contributors)

## License

The MIT License (MIT).
See [LICENSE](LICENSE.md) for more information.
