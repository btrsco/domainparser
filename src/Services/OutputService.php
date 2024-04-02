<?php

namespace DomainParser\Services;

use DomainParser\DataTransferObjects\OptionsDto;

class OutputService
{
    public function __construct(
        readonly protected OptionsDto $options
    ) {}

    public function format(array $data): object|array|string
    {
        if ($this->options->idnOutputFormat !== 'both') {
            $data = $this->formatIdn($data);
        }

        return match ($this->options->outputFormat) {
            'object' => (object)$data,
            'array' => $data,
            'json' => json_encode($data),
            'serialize' => serialize($data),
        };
    }

    private function formatIdn(array $data): array
    {
        $idnOutputFormat = $this->options->idnOutputFormat;

        return [
            "valid_hostname" => $data["valid_hostname"],
            "fqdn"           => $data["fqdn"][$idnOutputFormat],
            "subdomains"     => $data["subdomains"][$idnOutputFormat],
            "domain"         => $data["domain"][$idnOutputFormat],
            "extension"      => [
                "group" => $data["extension"]["group"][$idnOutputFormat],
                "full"  => $data["extension"]["full"][$idnOutputFormat],
            ],
        ];
    }
}
