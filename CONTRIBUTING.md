# Contributing to Laravel Fawaterk

Thank you for your interest in contributing to Laravel Fawaterk. This document outlines the guidelines and workflows for submitting contributions to this project.

## Overview

This package is a lightweight, clean abstraction for the Fawaterk API. Our goal is to provide a reliable, maintainable wrapper that remains close to the official API documentation. Contributions should adhere to this philosophy, avoiding over-abstraction and unnecessary enterprise patterns. Consistency with the official documentation is paramount.

## Development Environment

- **PHP:** 8.2 or higher.
- **Dependencies:** Composer is required for dependency management.
- **Testing:** We use PHPUnit and Orchestra Testbench to facilitate testing within a Laravel environment.

To set up your local environment:
1. Clone the repository.
2. Run `composer install` to install dependencies.
3. Ensure all tests pass by running `./vendor/bin/phpunit`.

## Project Structure

- `src/`: Core package logic, including Endpoints, DTOs, and Service providers.
- `tests/`: Unit, integration, and endpoint tests.
- `DOC/`: Source of truth for API integration.
- `config/`: Package configuration templates.

## Development Workflow

- **Branching:** Use descriptive branch names (e.g., `feature/add-invoice-id`, `fix/webhook-signature`).
- **Commits:** Write clear, concise, and atomic commits. Focus on the "why" of the change rather than just the "what".
- **Isolation:** Keep changes focused on a single feature or bug fix.

## Coding Standards

- **Strict Types:** Every file must declare `declare(strict_types=1);`.
- **Type Safety:** Use strict typing for all properties, parameters, and return values.
- **DTOs:** All requests and responses must use dedicated Data Transfer Objects.
- **Exception Handling:** Use the provided exception classes (`ApiException`, `RequestException`, etc.) consistently.
- **Standards:** Adhere to PSR-12 and use Laravel Pint where applicable.

## Documentation Rules

The `DOC/` directory is the source of truth for all API integrations.
- **Consistency:** Never invent endpoints not documented in `DOC/`.
- **API Behavior:** If real-world API behavior differs from the official documentation, update the documentation in `DOC/` and note it in your PR.
- **Sync:** All PRs must include updates to `README.md` and `CHANGELOG.md` if the change impacts public usage or package versioning.

## Testing Requirements

- **Unit Tests:** All new logic must be covered by unit tests.
- **Integration Tests:** Ensure changes do not break integration with the Fawaterk API.
- **Webhook Verification:** Any changes to webhooks must include verification tests using provided signatures.
- **Quality:** Ensure no regressions are introduced; the entire test suite must pass before submission.

## Pull Request Guidelines

Before submitting a Pull Request:
1. Ensure your code follows the coding standards.
2. All existing tests pass, and new tests are added for your changes.
3. Update relevant documentation in `DOC/` and the `README.md` if applicable.
4. Maintain backward compatibility.

## Release Process

- **Versioning:** We follow Semantic Versioning.
- **Changelog:** All changes must be recorded in `CHANGELOG.md` under the appropriate version heading.
- **Tagging:** Releases are tagged based on the version defined in `composer.json`.

## Security Reporting

If you identify a security vulnerability, please do not disclose it publicly. Report it privately via the repository's issue tracker or by contacting the maintainer directly. We prioritize responsible disclosure and will work to address verified issues as quickly as possible.

## License

By contributing, you agree that your contributions will be licensed under the MIT License.
