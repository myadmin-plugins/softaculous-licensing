# MyAdmin Softaculous Licensing

[![Tests](https://github.com/detain/myadmin-softaculous-licensing/actions/workflows/tests.yml/badge.svg)](https://github.com/detain/myadmin-softaculous-licensing/actions/workflows/tests.yml)
[![Latest Stable Version](https://poser.pugx.org/detain/myadmin-softaculous-licensing/version)](https://packagist.org/packages/detain/myadmin-softaculous-licensing)
[![Total Downloads](https://poser.pugx.org/detain/myadmin-softaculous-licensing/downloads)](https://packagist.org/packages/detain/myadmin-softaculous-licensing)
[![License](https://poser.pugx.org/detain/myadmin-softaculous-licensing/license)](https://packagist.org/packages/detain/myadmin-softaculous-licensing)

A PHP library for managing Softaculous, Webuzo, Virtualizor, and SiteMush licenses through the Softaculous NOC API. This package provides a MyAdmin plugin integration layer along with a standalone API client for license lifecycle operations including purchasing, renewal, cancellation, refunds, IP management, and auto-renewal configuration.

## Features

- Full Softaculous NOC API client (`SoftaculousNOC`) supporting Softaculous, Webuzo, Virtualizor, and SiteMush products
- MyAdmin plugin integration with event-driven hooks for license activation, deactivation, and IP changes
- XML/Array conversion utilities (`ArrayToXML`)
- Invoice and billing transaction management
- Auto-renewal management

## Requirements

- PHP 8.2 or higher
- ext-soap
- ext-curl
- ext-simplexml

## Installation

Install via Composer:

```sh
composer require detain/myadmin-softaculous-licensing
```

## Usage

```php
use Detain\MyAdminSoftaculous\SoftaculousNOC;

$noc = new SoftaculousNOC('your-username', 'your-password');

// Purchase a license
$result = $noc->buy('198.198.198.198', '1M', 1, 'admin@example.com', 1);

// List all licenses
$licenses = $noc->licenses();

// Cancel a license by key
$noc->cancel('88888-88888-88888-88888-88888');
```

## Running Tests

```sh
composer install
vendor/bin/phpunit
```

## License

This package is licensed under the LGPL-2.1 license.
