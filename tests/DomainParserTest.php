<?php

use DomainParser\DataTransferObjects\OptionsDto;
use DomainParser\DomainParser;
use DomainParser\Services\OutputService;

$defaultOptions = [
    'cache_path'      => sys_get_temp_dir(),
    'cache_filename'  => 'domainparser_tlds.json',
    'cache_lifetime'  => 604800,
    'list_url'        => 'https://publicsuffix.org/list/effective_tld_names.dat',
    'list_start'      => '// ===BEGIN ICANN DOMAINS===',
    'list_end'        => '// ===END ICANN DOMAINS===',
    'list_skip'       => ['//', '!'],
    'list_verify_ssl' => true,
];

$baseData = [
    'valid_hostname' => true,
    'fqdn'           => [
        'ascii'   => 'www.xn--caf-dma.com',
        'unicode' => 'www.café.com',
    ],
    'subdomains'     => [
        'ascii'   => ['www'],
        'unicode' => ['www'],
    ],
    'domain'         => [
        'ascii'   => 'xn--caf-dma',
        'unicode' => 'café',
    ],
    'extension'      => [
        'group' => [
            'ascii'   => 'com',
            'unicode' => 'com',
        ],
        'full'  => [
            'ascii'   => 'com',
            'unicode' => 'com',
        ],
    ],
];

$expectedAsciiData = [
    'valid_hostname' => true,
    'fqdn'           => 'www.xn--caf-dma.com',
    'subdomains'     => ['www'],
    'domain'         => 'xn--caf-dma',
    'extension'      => [
        'group' => 'com',
        'full'  => 'com',
    ],
];

$expectedUnicodeData = [
    'valid_hostname' => true,
    'fqdn'           => 'www.café.com',
    'subdomains'     => ['www'],
    'domain'         => 'café',
    'extension'      => [
        'group' => 'com',
        'full'  => 'com',
    ],
];

function setupOutputService(array $options): OutputService
{
    $optionsDto = OptionsDto::fromArray($options);

    return new OutputService($optionsDto);
}

function setupDomainParser(array $options): DomainParser
{
    return new DomainParser($options);
}

it('returns correct output with object/both', function () use ($defaultOptions, $baseData) {
    $outputService = setupOutputService([
        'output_format'     => 'object',
        'idn_output_format' => 'both',
        ...$defaultOptions,
    ]);
    $expected      = (object)$baseData;

    expect($outputService->format($baseData))->toEqual($expected);
});

it('returns correct output with array/both', function () use ($defaultOptions, $baseData) {
    $outputService = setupOutputService([
        'output_format'     => 'array',
        'idn_output_format' => 'both',
        ...$defaultOptions,
    ]);
    $expected      = $baseData;

    expect($outputService->format($baseData))->toEqual($expected);
});

it('returns correct output with json/both', function () use ($defaultOptions, $baseData) {
    $outputService = setupOutputService([
        'output_format'     => 'json',
        'idn_output_format' => 'both',
        ...$defaultOptions,
    ]);

    $expected = json_encode($baseData);

    expect($outputService->format($baseData))->toEqual($expected);
});

it('returns correct output with serialize/both', function () use ($defaultOptions, $baseData) {
    $outputService = setupOutputService([
        'output_format'     => 'serialize',
        'idn_output_format' => 'both',
        ...$defaultOptions,
    ]);

    $expected = serialize($baseData);

    expect($outputService->format($baseData))->toEqual($expected);
});

it('returns correct output with object/ascii', function () use ($defaultOptions, $baseData, $expectedAsciiData) {
    $outputService = setupOutputService([
        'output_format'     => 'object',
        'idn_output_format' => 'ascii',
        ...$defaultOptions,
    ]);

    $expected = (object)$expectedAsciiData;

    expect($outputService->format($baseData))->toEqual($expected);
});

it('returns correct output with array/ascii', function () use ($defaultOptions, $baseData, $expectedAsciiData) {
    $outputService = setupOutputService([
        'output_format'     => 'array',
        'idn_output_format' => 'ascii',
        ...$defaultOptions,
    ]);

    $expected = $expectedAsciiData;

    expect($outputService->format($baseData))->toEqual($expected);
});

it('returns correct output with json/ascii', function () use ($defaultOptions, $baseData, $expectedAsciiData) {
    $outputService = setupOutputService([
        'output_format'     => 'json',
        'idn_output_format' => 'ascii',
        ...$defaultOptions,
    ]);

    $expected = json_encode($expectedAsciiData);

    expect($outputService->format($baseData))->toEqual($expected);
});

it('returns correct output with serialize/ascii', function () use ($defaultOptions, $baseData, $expectedAsciiData) {
    $outputService = setupOutputService([
        'output_format'     => 'serialize',
        'idn_output_format' => 'ascii',
        ...$defaultOptions,
    ]);

    $expected = serialize($expectedAsciiData);

    expect($outputService->format($baseData))->toEqual($expected);
});

it('returns correct output with object/unicode', function () use ($defaultOptions, $baseData, $expectedUnicodeData) {
    $outputService = setupOutputService([
        'output_format'     => 'object',
        'idn_output_format' => 'unicode',
        ...$defaultOptions,
    ]);

    $expected = (object)$expectedUnicodeData;

    expect($outputService->format($baseData))->toEqual($expected);
});

it('returns correct output with array/unicode', function () use ($defaultOptions, $baseData, $expectedUnicodeData) {
    $outputService = setupOutputService([
        'output_format'     => 'array',
        'idn_output_format' => 'unicode',
        ...$defaultOptions,
    ]);

    $expected = $expectedUnicodeData;

    expect($outputService->format($baseData))->toEqual($expected);
});

it('returns correct output with json/unicode', function () use ($defaultOptions, $baseData, $expectedUnicodeData) {
    $outputService = setupOutputService([
        'output_format'     => 'json',
        'idn_output_format' => 'unicode',
        ...$defaultOptions,
    ]);

    $expected = json_encode($expectedUnicodeData);

    expect($outputService->format($baseData))->toEqual($expected);
});

it('returns correct output with serialize/unicode', function () use ($defaultOptions, $baseData, $expectedUnicodeData) {
    $outputService = setupOutputService([
        'output_format'     => 'serialize',
        'idn_output_format' => 'unicode',
        ...$defaultOptions,
    ]);

    $expected = serialize($expectedUnicodeData);

    expect($outputService->format($baseData))->toEqual($expected);
});

it('returns correctly parsed output with object/both', function () use ($defaultOptions, $baseData) {
    $domainParser = setupDomainParser([
        'output_format'     => 'object',
        'idn_output_format' => 'both',
        ...$defaultOptions,
    ]);

    $expected = (object)$baseData;

    expect($domainParser->parse('www.café.com'))->toEqual($expected);
});

it('returns correctly parsed output with array/both', function () use ($defaultOptions, $baseData) {
    $domainParser = setupDomainParser([
        'output_format'     => 'array',
        'idn_output_format' => 'both',
        ...$defaultOptions,
    ]);

    $expected = $baseData;

    expect($domainParser->parse('www.café.com'))->toEqual($expected);
});

it('returns correctly parsed output with json/both', function () use ($defaultOptions, $baseData) {
    $domainParser = setupDomainParser([
        'output_format'     => 'json',
        'idn_output_format' => 'both',
        ...$defaultOptions,
    ]);

    $expected = json_encode($baseData);

    expect($domainParser->parse('www.café.com'))->toEqual($expected);
});

it('returns correctly parsed output with serialize/both', function () use ($defaultOptions, $baseData) {
    $domainParser = setupDomainParser([
        'output_format'     => 'serialize',
        'idn_output_format' => 'both',
        ...$defaultOptions,
    ]);

    $expected = serialize($baseData);

    expect($domainParser->parse('www.café.com'))->toEqual($expected);
});

it(
    'returns correctly parsed output with object/ascii',
    function () use ($defaultOptions, $baseData, $expectedAsciiData) {
        $domainParser = setupDomainParser([
            'output_format'     => 'object',
            'idn_output_format' => 'ascii',
            ...$defaultOptions,
        ]);

        $expected = (object)$expectedAsciiData;

        expect($domainParser->parse('www.café.com'))->toEqual($expected);
    }
);

it(
    'returns correctly parsed output with array/ascii',
    function () use ($defaultOptions, $baseData, $expectedAsciiData) {
        $domainParser = setupDomainParser([
            'output_format'     => 'array',
            'idn_output_format' => 'ascii',
            ...$defaultOptions,
        ]);

        $expected = $expectedAsciiData;

        expect($domainParser->parse('www.café.com'))->toEqual($expected);
    }
);

it('returns correctly parsed output with json/ascii', function () use ($defaultOptions, $baseData, $expectedAsciiData) {
    $domainParser = setupDomainParser([
        'output_format'     => 'json',
        'idn_output_format' => 'ascii',
        ...$defaultOptions,
    ]);

    $expected = json_encode($expectedAsciiData);

    expect($domainParser->parse('www.café.com'))->toEqual($expected);
});

it(
    'returns correctly parsed output with serialize/ascii',
    function () use ($defaultOptions, $baseData, $expectedAsciiData) {
        $domainParser = setupDomainParser([
            'output_format'     => 'serialize',
            'idn_output_format' => 'ascii',
            ...$defaultOptions,
        ]);

        $expected = serialize($expectedAsciiData);

        expect($domainParser->parse('www.café.com'))->toEqual($expected);
    }
);

it(
    'returns correctly parsed output with object/unicode',
    function () use ($defaultOptions, $baseData, $expectedUnicodeData) {
        $domainParser = setupDomainParser([
            'output_format'     => 'object',
            'idn_output_format' => 'unicode',
            ...$defaultOptions,
        ]);

        $expected = (object)$expectedUnicodeData;

        expect($domainParser->parse('www.café.com'))->toEqual($expected);
    }
);

it(
    'returns correctly parsed output with array/unicode',
    function () use ($defaultOptions, $baseData, $expectedUnicodeData) {
        $domainParser = setupDomainParser([
            'output_format'     => 'array',
            'idn_output_format' => 'unicode',
            ...$defaultOptions,
        ]);

        $expected = $expectedUnicodeData;

        expect($domainParser->parse('www.café.com'))->toEqual($expected);
    }
);

it(
    'returns correctly parsed output with json/unicode',
    function () use ($defaultOptions, $baseData, $expectedUnicodeData) {
        $domainParser = setupDomainParser([
            'output_format'     => 'json',
            'idn_output_format' => 'unicode',
            ...$defaultOptions,
        ]);

        $expected = json_encode($expectedUnicodeData);

        expect($domainParser->parse('www.café.com'))->toEqual($expected);
    }
);

it(
    'returns correctly parsed output with serialize/unicode',
    function () use ($defaultOptions, $baseData, $expectedUnicodeData) {
        $domainParser = setupDomainParser([
            'output_format'     => 'serialize',
            'idn_output_format' => 'unicode',
            ...$defaultOptions,
        ]);

        $expected = serialize($expectedUnicodeData);

        expect($domainParser->parse('www.café.com'))->toEqual($expected);
    }
);
