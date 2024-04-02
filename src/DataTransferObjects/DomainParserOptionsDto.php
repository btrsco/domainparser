<?php

namespace DomainParser\DataTransferObjects;

class DomainParserOptionsDto
{
    public function __construct(
        readonly public string $outputFormat,
        readonly public string $idnOutputFormat,
        readonly public string $cachePath,
        readonly public string $cacheFilename,
        readonly public int $cacheTtl,
        readonly public string $listUrl,
        readonly public string $listStart,
        readonly public string $listEnd,
        readonly public array $listSkip,
        readonly public bool $listVerifySsl,
    ) {}

    public static function fromArray(array $options): DomainParserOptionsDto
    {
        return new self(
            $options['output_format'] ?? config('domainparser.output_format'),
            $options['idn_output_format'] ?? config('domainparser.idn_output_format'),
            $options['cache_path'] ?? config('domainparser.cache.path'),
            $options['cache_filename'] ?? config('domainparser.cache.filename'),
            $options['cache_lifetime'] ?? config('domainparser.cache.lifetime'),
            $options['list_url'] ?? config('domainparser.list.url'),
            $options['list_start'] ?? config('domainparser.list.start'),
            $options['list_end'] ?? config('domainparser.list.end'),
            $options['list_skip'] ?? config('domainparser.list.skip'),
            $options['list_verify_ssl'] ?? config('domainparser.list.verify_ssl'),
        );
    }

    public static function fromConfig(): DomainParserOptionsDto
    {
        return new self(
            config('domainparser.output_format'),
            config('domainparser.idn_output_format'),
            config('domainparser.cache.path'),
            config('domainparser.cache.filename'),
            config('domainparser.cache.lifetime'),
            config('domainparser.list.url'),
            config('domainparser.list.start'),
            config('domainparser.list.end'),
            config('domainparser.list.skip'),
            config('domainparser.list.verify_ssl'),
        );
    }
}
