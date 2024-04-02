<?php

namespace DomainParser;

use DomainParser\DataTransferObjects\OptionsDto;
use DomainParser\Exceptions\ExtensionNotFoundException;
use DomainParser\Exceptions\InvalidDomainException;
use DomainParser\Services\OutputService;
use DomainParser\Services\TldListService;

class DomainParser
{
    private OptionsDto     $options;
    private TldListService $tldList;
    private OutputService  $output;

    public function __construct(array|OptionsDto $options = [])
    {
        $this->options = $options instanceof OptionsDto
            ? $options
            : OptionsDto::fromArray($options);

        $this->tldList = new TldListService($this->options);
        $this->output  = new OutputService($this->options);
    }

    /**
     * @param string $dirtyDomain
     *
     * @return object|array|string
     * @throws ExtensionNotFoundException
     * @throws InvalidDomainException
     */
    public function parse(string $dirtyDomain): object|array|string
    {
        $list   = $this->tldList->load();
        $domain = $this->cleanDomain($dirtyDomain);

        if ( ! $domain) {
            throw new InvalidDomainException('Unable to parse domain from the input string');
        }

        $extension = $this->parseExtension($domain, $list);

        if ( ! $extension) {
            throw new ExtensionNotFoundException('Extension not found in the list');
        }

        $segments = $this->parseSegments($domain, $extension['full']);
        $valid    = $this->validateHostname($domain);

        return $this->output->format([
            'valid_hostname' => $valid,
            'fqdn'           => [
                'ascii'   => idn_to_ascii($domain),
                'unicode' => idn_to_utf8($domain),
            ],
            'subdomains'     => [
                'ascii'   => array_map('idn_to_ascii', $segments['subdomains']),
                'unicode' => array_map('idn_to_utf8', $segments['subdomains']),
            ],
            'domain'         => [
                'ascii'   => idn_to_ascii($segments['domain']),
                'unicode' => idn_to_utf8($segments['domain']),
            ],
            'extension'      => [
                'group' => [
                    'ascii'   => idn_to_ascii($extension['group']),
                    'unicode' => idn_to_utf8($extension['group']),
                ],
                'full'  => [
                    'ascii'   => idn_to_ascii($extension['full']),
                    'unicode' => idn_to_utf8($extension['full']),
                ],
            ],
        ]);
    }

    private function cleanDomain(string $dirtyDomain): ?string
    {
        $urlParts = parse_url($dirtyDomain);

        if (isset($urlParts['host'])) {
            return $urlParts['host'];
        }

        if ( ! isset($urlParts['scheme'])) {
            return $this->cleanDomain('http://' . $dirtyDomain);
        }

        return null;
    }

    private function parseExtension(string $domain, array $tldList): ?array
    {
        foreach ($tldList['list'] as $parent => $children) {
            $fragments = explode('.', $domain);
            $tldIndex  = count($fragments) - 1;

            while ($tldIndex >= 0) {
                $tld = implode('.', array_slice($fragments, $tldIndex - 1));

                if (in_array($tld, $children)) {
                    return [
                        'group' => $parent,
                        'full'  => $tld,
                    ];
                }

                $tldIndex--;
            }
        }

        return null;
    }

    private function parseSegments(string $domain, string $extension): array
    {
        $domain = str_replace($extension, '', $domain);
        $domain = trim($domain, '.');
        $levels = explode('.', $domain);
        $name   = end($levels);

        unset($levels[array_search($name, $levels)]);

        return [
            'domain'     => $name,
            'subdomains' => empty($levels) ? [] : $levels,
        ];
    }

    private function validateHostname(string $hostname): bool
    {
        $hostname  = idn_to_ascii($hostname);
        $fragments = explode('.', $hostname);

        foreach ($fragments as $value) {
            $length = strlen($value);
            preg_match("/^[a-zA-Z0-9-]+$/s", $value, $matches);

            if ($length > 63 || empty($matches)) {
                return false;
            }
        }

        if (strlen($hostname) > 253) {
            return false;
        }

        return true;
    }
}
