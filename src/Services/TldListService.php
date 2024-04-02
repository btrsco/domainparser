<?php

namespace DomainParser\Services;

use DomainParser\DataTransferObjects\DomainParserOptionsDto;
use DomainParser\Helpers\ArrayHelpers;
use DomainParser\Helpers\StringHelpers;

class TldListService
{
    public function __construct(
        readonly protected DomainParserOptionsDto $options
    ) {}

    public function load(): array
    {
        $list = $this->get();

        if ( ! $list || $this->isExpired($list['last_updated'])) {
            $this->reload();
            $list = $this->get();
        }

        return $list;
    }

    public function reload(): void
    {
        $startComment = $this->options->listStart;
        $endComment   = $this->options->listEnd;

        $rawList = $this->getRawList();
        $rawList = StringHelpers::between($rawList, $startComment, $endComment);

        $lines         = explode("\n", $rawList);
        $lines         = ArrayHelpers::removeStartsWith($lines, $this->options->listSkip);
        $formattedList = ArrayHelpers::groupTlds($lines);

        $this->cache([
            'last_updated' => time(),
            'list'         => $formattedList,
        ]);
    }

    public function has(): bool
    {
        return file_exists($this->cacheFilePath());
    }

    public function get(): ?array
    {
        if ( ! $this->has()) {
            return null;
        }

        return json_decode(file_get_contents($this->cacheFilePath()), true);
    }

    public function cache($data): void
    {
        file_put_contents($this->cacheFilePath(), json_encode($data));
    }

    private function cacheFilePath(): string
    {
        $cachePath = rtrim($this->options->cachePath, '/');
        $fileName  = ltrim($this->options->cacheFilename, '/');

        return "$cachePath/$fileName";
    }

    private function isExpired($lastUpdated): bool
    {
        return time() - $lastUpdated > $this->options->cacheTtl;
    }

    private function getRawList(): bool|string
    {
        return file_get_contents(filename: $this->options->listUrl, context: stream_context_create([
            'ssl' => [
                'verify_peer'      => $this->options->listVerifySsl,
                'verify_peer_name' => $this->options->listVerifySsl,
            ],
        ]));
    }

}
