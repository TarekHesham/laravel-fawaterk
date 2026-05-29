# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.0] - 2026-05-29

### Added

- WebhookType enum for strongly typed webhook handling.
- Paid webhook DTO support based on real Fawaterk webhook payloads.
- Custom webhook URL support in Init Pay requests.
- End-to-end webhook signature verification workflow.
- Additional integration coverage for payment verification flows.

### Changed

- Replaced string-based webhook types with enum-based implementation.
- Refactored webhook parsing flow to use `verifyAndParse()`.
- Improved payment gateway developer experience and endpoint naming.
- Updated webhook payload mapping to reflect actual Fawaterk responses.
- Enhanced response DTO handling and type safety across the package.
- Improved package architecture and internal service organization.

### Fixed

- Fixed webhook signature verification to use the correct API secret key.
- Fixed paid webhook payload parsing and DTO mapping.
- Fixed canceled webhook signature validation.
- Fixed invoice verification workflow and related tests.
- Fixed request and response DTO serialization edge cases.
- Fixed integration and unit test inconsistencies after endpoint refactoring.

### Notes

- Webhook signature verification uses the Fawaterk API Secret Key.
- Failed card payments may redirect users to the configured fail URL without triggering a webhook event.
- Tested and verified against real Fawaterk payment and webhook responses.

## [1.0.0] - 2026-05-20

### Added

- Initial release of Laravel Fawaterk API wrapper.
- Full support for Fawaterk Invoice API (creation and retrieval).
- Payment Gateway support for Redirect, Fawry, and Meeza.
- Tokenization management support.
- Secure webhook signature verification and payload DTO parsing.
- Comprehensive set of Data Transfer Objects (DTOs) for all supported endpoints.
- Custom exception handling for API and integration errors.
- Package configuration and Facade support.
- Complete unit and integration test suite.

### Notes

- This release marks the completion of the initial audit and stabilization phase.
