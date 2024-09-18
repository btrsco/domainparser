# Domain Parser

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]
[![License][ico-license]][link-license]

Domain Parser simply parses a domain name you supply.

During the parsing process, the package will download a list of all [Public Suffixes](https://publicsuffix.org/list/public_suffix_list.dat) provided by [Mozilla](https://www.mozilla.org), iterate through it and save it to the systems temp folder and will update this list after a week. Next, the supplied domain will get parsed and broken up into parts and compared to the saved suffix list to determine the TLD the domain is using, then will complete a sanity check on the domain to ensure validity of it. Once this is finished, you'll receive an object containing all relevant information on the domain and its parts.

## Installation

Install this package via composer:

``` bash
$ composer require warden/domain-parser
```

This service provider must be installed (if using anything below Laravel 5.5)

``` php
// config/app.php

'providers' => [
    DomainParser\DomainParserServiceProvider::class,
];
```

Publish and customize configuration file with:

``` bash
$ php artisan vendor:publish --provider="DomainParser\DomainParserServiceProvider"
```

## Usage

Create new `DomainParser` object:

``` php
use DomainParser\DomainParser;
...
$domainParser = new DomainParser( $options = [] );
```

Then call `parse()` method to parse the domain:

``` php
$domainParser->parse( 'www.example.com' );
```

Here is an example of the output:

``` php
[
    'valid_hostname' => true,
    'fqdn' => [
        'ascii' => 'www.example.com',
        'idn' => 'www.example.com'
    ],
    'sub_domains' => [
        'ascii' => [
            0 => 'www'
        ],
        'idn' => [
            0 => 'www'
        ]
    ],
    'domain' => [
        'ascii' => 'example',
        'idn' => 'example'
    ],
    'tld' => [
        'group' => [
            'ascii' => 'com',
            'idn' => 'com'
        ],
        'tld' => [
            'ascii' => 'com',
            'idn' => 'com'
        ]
    ]
]
```

### Options

When creating the `DomainParser` object, there is only one `array` parameter that can be passed, which is *optional*.

Options array parameters:

| Option            | Notes                                            | Type     | Default                                                                     |
|-------------------|--------------------------------------------------|----------|-----------------------------------------------------------------------------|
| `output_format`   | options (`object`, `array`, `json`, `serialize`) | `string` | `object` |
| `cache_path`      | absolute path                                    | `string` | `sys_get_temp_dir()`                                                        |
| `cache_life_time` | in seconds                                       | `int`    | `604800` (7 Days)                                                           |
| `list_url`        | url to suffix list                               | `string` | [Public Suffix List](https://publicsuffix.org/list/effective_tld_names.dat) |
| `list_start`      | start of suffix list                             | `string` | `// ===BEGIN ICANN DOMAINS===`                                              |
| `list_end`        | end of suffix list                               | `string` | `// ===END ICANN DOMAINS===`                                                |
| `list_remove`     | remove items that start with                     | `array`  | `['//', '!']`                                                               |

Instead of setting these options when creating the object, you can alternatively set these globally in the configuration file. You can publish the configuration and customize it as shown in the [Installation](#installation) section.

## Changelog

Please see the [changelog](changelog.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [contributing.md](contributing.md) for details and a todolist.

## Security

If you discover any security related issues, please email [hello@xand.co](mailto:hello@xand.co) instead of using the issue tracker.

## Credits

- [X&Co][link-company]
- [Miguel Batres][link-author]
- [All Contributors][link-contributors]

## License

MIT - Please see the [license file](license.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/warden/domain-parser.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/warden/domain-parser.svg?style=flat-square
[ico-license]: https://img.shields.io/packagist/l/warden/domain-parser?style=flat-square

[link-packagist]: https://packagist.org/packages/warden/domain-parser
[link-downloads]: https://packagist.org/packages/warden/domain-parser
[link-author]: https://github.com/btrsco
[link-company]: https://github.com/warden
[link-license]: https://github.com/warden/domain-parser/blob/master/license.md
[link-contributors]: ../../contributors
