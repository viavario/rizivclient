# viavario/rizivclient

A lightweight PHP client for searching healthcare professionals on the official RIZIV/INAMI silver pages by registration number.

## Requirements

- PHP `^7.4 | ^8.0`
- Extensions: `ext-curl`, `ext-dom`

## Installation

```bash
composer require viavario/rizivclient
```

## What this library does

### `RizivClient`

`RizivClient` is responsible for:

- sanitizing a registration number input (removing non-digits)
- building the search query expected by the RIZIV website
- performing the HTTP GET request
- parsing the returned HTML with `DOMDocument` + `DOMXPath`
- mapping the first result card into a `RizivResult` object

If no result card is found, it returns `null`.
If the HTTP request fails (cURL failure or non-2xx status), it throws `\RuntimeException`.

### `RizivResult`

`RizivResult` is a simple data object that represents a single healthcare professional result.

It stores:

- `name` (`string`)
- `riziv_number` (`string`)
- `profession` (`string`)
- `contracted` (`bool`) — derived from whether the site returns `geconventioneerd`
- `qualification` (`string`)
- `qualification_date` (`\DateTime`)

It also provides `toArray()` to export those values as an associative array.

## Usage

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use viavario\rizivclient\RizivClient;

$client = new RizivClient();
$result = $client->searchByRegistrationNumber('731106598');

if ($result === null) {
    echo "No healthcare professional found." . PHP_EOL;
    exit;
}

echo "Name: " . $result->name . PHP_EOL;
echo "RIZIV number: " . $result->riziv_number . PHP_EOL;
echo "Profession: " . $result->profession . PHP_EOL;
echo "Contracted: " . ($result->contracted ? 'Yes' : 'No') . PHP_EOL;
echo "Qualification: " . $result->qualification . PHP_EOL;
echo "Qualification date: " . $result->qualification_date->format('Y-m-d') . PHP_EOL;
```

## API

### `RizivClient`

#### `searchByRegistrationNumber(string $registrationNumber): ?RizivResult`

Searches by RIZIV registration number and returns the first match as `RizivResult`, or `null` if no match is found.

Possible exceptions:

- `\RuntimeException` when cURL fails
- `\RuntimeException` when the server responds with a non-2xx status code

### `RizivResult`

| Property | Type |
|---|---|
| `$name` | `string` |
| `$riziv_number` | `string` |
| `$profession` | `string` |
| `$contracted` | `bool` |
| `$qualification` | `string` |
| `$qualification_date` | `\DateTime` |

#### `toArray(): array`

Returns:

```php
[
    'name'               => 'Test Name',
    'riziv_number'       => '12345678',
    'profession'         => 'Doctor',
    'contracted'         => true,
    'qualification'      => 'MD',
    'qualification_date' => new \DateTime('2020-01-01'),
]
```

## Development

Install dependencies:

```bash
composer install
```

Run tests:

```bash
./vendor/bin/phpunit
```

## License

MIT — see [LICENSE](LICENSE) for details.

## Disclaimer

This package depends on the current HTML structure and labels of the RIZIV/INAMI website. If the upstream markup changes, parsing may need updates.
