# PHP CI/CD Lab

[![PHP CI Application](https://github.com/SydorchukAndrii/php-lab1/actions/workflows/php-ci.yml/badge.svg)](https://github.com/SydorchukAndrii/php-lab1/actions/workflows/php-ci.yml)

## Description

Laboratory Work No. 1: Version Control and CI/CD Pipeline Setup for a PHP Project.

The project demonstrates automated code quality checks and unit testing using GitHub Actions.

## Features

- Version control with Git and GitHub
- PHP calculator with addition and subtraction
- Automated unit testing with PHPUnit
- Code style validation with Laravel Pint
- CI pipeline using GitHub Actions
- Secure handling of GitHub Secrets

## Project Structure

- `app/Services/Calculator.php` - calculator implementation
- `tests/Unit/CalculatorTest.php` - unit tests
- `.github/workflows/php-ci.yml` - CI configuration
- `composer.json` - dependencies

## Running Tests

Install dependencies:

```bash
composer install
```

Run unit tests:

```bash
./vendor/bin/phpunit --bootstrap vendor/autoload.php tests/Unit
```

Run code style checks:

```bash
./vendor/bin/pint --test
```

## CI/CD

The pipeline automatically runs when changes are pushed to the main branch or a Pull Request is created.

The pipeline performs:

1. PHP environment setup
2. Dependency installation
3. Code style validation
4. Unit testing
5. GitHub Secrets demonstration
