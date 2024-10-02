# Laravel Preset

Laravel Preset is opinionated coding preset for Laravel.

## Features

- [x] [Larastan](https://github.com/larastan/larastan)
- [x] [Laravel Pint](https://laravel.com/docs/11.x/pint) with [strict preset](https://github.com/nunomaduro/pint-strict-preset)
- [x] [Laravel Telescope](https://laravel.com/docs/11.x/telescope)
- [x] [Pest](https://pestphp.com/)
- [x] [Rector](https://getrector.com/)

## Requirements

Laravel Preset requires the following to run:

- PHP 8.2+
- Laravel v11.0+

## Installation

> Since these commands will overwrite existing files in your application, only run this in a new Laravel project!

Require the Laravel Preset package using Composer:

```bash
composer require fabpl/laravel-preset --dev

php preset:install
```

## Usage

This command will be available after installing the Laravel Preset package:

```bash
# Run PHPStan
composer analyze

# Run all the tools
composer check

# Run the coverage test suite
composer coverage

# Lint the code using Pint
composer lint

# Refactor the code using Rector
composer refactor

# Run the test suite
composer test
```