Domain Parser
---

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]
[![License][ico-license]][link-license]

---

<!-- TOC -->
  * [Domain Parser](#domain-parser)
  * [Introduction](#introduction)
  * [Installation](#installation)
  * [Usage](#usage)
    * [Configuration](#configuration)
  * [Changelog](#changelog)
  * [Testing](#testing)
  * [Credits](#credits)
  * [License](#license)
<!-- TOC -->

---

## Introduction
Parse domains quickly with this package. This `xandco/domainparser` package is a simple package that allows you to get
the domain name, subdomains, extension and fully qualified domain name from a given URL.


## Installation
Install this package via composer:

```bash
composer require xandco/domainparser
```

You can publish the configuration file using the following command:

```bash
php artisan vendor:publish --provider="DomainParser\DomainParserServiceProvider"
```

There's no need to publish the configuration file, you can always provide the configuration values directly to the `DomainParser` class — [shown below](#configuration).


## Usage
You can use the `DomainParser` class to parse a domain from a given URL.

```php
use DomainParser\DomainParser;

$domainParser = new DomainParser();
return $domainParser->parse('https://www.café.co.uk/');
```

The above code will return the following array:

```php
[
    "valid_hostname" => true,
    "fqdn"           => [
        "ascii"   => "www.xn--caf-dma.com",
        "unicode" => "www.café.com",
    ],
    "subdomains"     => [
        "ascii"   => ["www"],
        "unicode" => ["www"],
    ],
    "domain"         => [
        "ascii"   => "xn--caf-dma",
        "unicode" => "café",
    ],
    "extension"      => [
        "group" => [
            "ascii"   => "uk",
            "unicode" => "uk",
        ],
        "full"  => [
            "ascii"   => "co.uk",
            "unicode" => "co.uk",
        ],
    ],
]
```

The `parse` method will return an array with the following keys:

| Key               | Notes                          | Type      |
|-------------------|--------------------------------|-----------|
| `valid_hostname`  | Primitive validation           | `boolean` |
| `fqdn`            | ASCII and UFT8 FQDN            | `array`   |
| `subdomains`      | ASCII and UFT8 subdomains      | `array`   |
| `domain`          | ASCII and UFT8 domain          | `array`   |
| `extension.group` | ASCII and UFT8 extension group | `array`   |
| `extension.full`  | ASCII and UFT8 full extension  | `array`   |


### Configuration
You can provide the configuration values directly to the `DomainParser` class.

```php
use DomainParser\DomainParser;

$domainParser = new DomainParser([
    'output_format'     => 'array', // array, object, json, serialize
    'idn_output_format' => 'both', // ascii, unicode, both
    'cache_path'        => sys_get_temp_dir(),
    'cache_filename'   => 'domainparser_tlds.json',
    'cache_lifetime'   => 604800, // 1 week
    'list_url'          => 'https://publicsuffix.org/list/effective_tld_names.dat',
    'list_start'        => '// ===BEGIN ICANN DOMAINS===',
    'list_end'          => '// ===END ICANN DOMAINS===',
    'list_skip'         => ['//', '!'],
    'list_verify_ssl'   => true,
]);
```

The `DomainParser` class accepts an array of configuration values. The following are the available configuration values:

| Option              | Notes                                            | Type      | Default                                                                     |
|---------------------|--------------------------------------------------|-----------|-----------------------------------------------------------------------------|
| `output_format`     | options (`array`, `object`, `json`, `serialize`) | `string`  | `array`                                                                     |
| `idn_output_format` | options (`both`, `ascii`, `unicode`)             | `string`  | `both`                                                                      |
| `cache_path`        | absolute path                                    | `string`  | `sys_get_temp_dir()`                                                        |
| `cache_filename`    | filename                                         | `string`  | `604800` (7 Days)                                                           |
| `cache_lifetime`    | in seconds                                       | `int`     | `604800` (7 Days)                                                           |
| `list_url`          | url to suffix list                               | `string`  | [Public Suffix List](https://publicsuffix.org/list/effective_tld_names.dat) |
| `list_start`        | start of suffix list                             | `string`  | `// ===BEGIN ICANN DOMAINS===`                                              |
| `list_end`          | end of suffix list                               | `string`  | `// ===END ICANN DOMAINS===`                                                |
| `list_skip`         | remove items that start with                     | `array`   | `['//', '!']`                                                               |
| `list_verify_ssl`   | Verify SSL when downloading tld list             | `boolean` | `true`                                                                      |

These configuration values also map to the configuration file `config/domainparser.php`, the keys are similar to the 
configuration keys above.

## Changelog
Please see [CHANGELOG](changelog.md) for more information on what has changed recently.

## Testing
You can run the tests using the following command:

```bash
composer test
```

The tests are located in the `tests` directory. You can add more tests to cover more scenarios, they are using Pest.


## Credits

- [X&Co][link-company]
- [Miguel Batres][link-author]
- [All Contributors][link-contributors]


## License

MIT - Please see the [license file](license.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/xandco/domainparser.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/xandco/domainparser.svg?style=flat-square
[ico-license]: https://img.shields.io/packagist/l/xandco/domainparser?style=flat-square

[link-packagist]: https://packagist.org/packages/xandco/domainparser
[link-downloads]: https://packagist.org/packages/xandco/domainparser
[link-author]: https://github.com/btrsco
[link-company]: https://github.com/xandco
[link-license]: https://github.com/xandco/domainparser/blob/master/license.md
[link-contributors]: ../../contributors
