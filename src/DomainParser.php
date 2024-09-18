<?php

namespace DomainParser;

use ErrorException;

class DomainParser
{
    /**
     * Output Format String ('object', 'array', 'json', 'serialize')
     *
     * @var string
     */
    protected string $_outputFormat;

    /**
     * Path to System Temp Directory
     *
     * @var string
     */
    protected string $_cachePath;

    /**
     * Cached File Lifetime in Seconds
     *
     * @var int
     */
    protected int $_cacheTime;

    /**
     * List All of Domain Extensions
     *
     * @var array
     */
    protected array $_extensionList = [];

    /**
     * Url of Top-Level Suffix List
     *
     * @var string
     */
    protected string $_suffixUrl;

    /**
     * Comment for Start of Suffix List
     *
     * @var string
     */
    protected string $_listStart;

    /**
     * Comment for End of Suffix List
     *
     * @var string
     */
    protected string $_listEnd;

    /**
     * List of line prefixes to remove line
     *
     * @var array
     */
    protected array $_listRemove;

    /**
     * DomainParser constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->setOutputFormat($options['output_format'] ?? config('domain-parser.output_format'));
        $this->setCachePath($options['cache_path'] ?? config('domain-parser.cache.path'));
        $this->setCacheTime($options['cache_life_time'] ?? config('domain-parser.cache.life_time'));
        $this->setSuffixUrl($options['list_url'] ?? config('domain-parser.list.url'));
        $this->setListStart($options['list_start'] ?? config('domain-parser.list.start'));
        $this->setListEnd($options['list_end'] ?? config('domain-parser.list.end'));
        $this->setListRemove($options['list_remove'] ?? config('domain-parser.list.remove'));
    }

    /**
     * Get Output Format String
     *
     * @return string
     */
    protected function getOutputFormat(): string
    {
        return $this->_outputFormat;
    }

    /**
     * Set Output Format String
     *
     * @param string $outputFormat
     */
    protected function setOutputFormat(string $outputFormat): void
    {
        $this->_outputFormat = $outputFormat;
    }

    /**
     * Get System Temp Path
     *
     * @param string $path
     *
     * @return string
     */
    protected function getCachePath(string $path = ''): string
    {
        return rtrim($this->_cachePath, '/') . '/' . ltrim($path, '/');
    }

    /**
     * Set System Temp Path
     *
     * @param string|null $cachePath
     */
    protected function setCachePath(string $cachePath = null): void
    {
        $this->_cachePath = $cachePath ?? sys_get_temp_dir();
    }

    /**
     * Get File Cache Life Time
     *
     * @return int
     */
    protected function getCacheTime(): int
    {
        return $this->_cacheTime;
    }

    /**
     * Set File Cache Life Time
     *
     * @param int $cacheTime
     */
    protected function setCacheTime(int $cacheTime): void
    {
        $this->_cacheTime = $cacheTime;
    }

    /**
     * Get List of Extension Suffixes
     *
     * @return array
     */
    protected function getExtensionList(): array
    {
        return $this->_extensionList;
    }

    /**
     * Set List of Extension Suffixes
     *
     * @param array $extensionList
     */
    protected function setExtensionList(array $extensionList): void
    {
        $this->_extensionList = $extensionList;
    }

    /**
     * Get Url of Extension Suffix List
     *
     * @return string
     */
    protected function getSuffixUrl(): string
    {
        return $this->_suffixUrl;
    }

    /**
     * Set Url of Extension Suffix List
     *
     * @param string $suffixUrl
     */
    protected function setSuffixUrl(string $suffixUrl): void
    {
        $this->_suffixUrl = $suffixUrl;
    }

    /**
     * Get Comment for Start of Suffix List
     *
     * @return string
     */
    protected function getListStart(): string
    {
        return $this->_listStart;
    }

    /**
     * Set Comment for Start of Suffix List
     *
     * @param string $listStart
     */
    protected function setListStart(string $listStart): void
    {
        $this->_listStart = $listStart;
    }

    /**
     * Get Comment for End of Suffix List
     *
     * @return string
     */
    protected function getListEnd(): string
    {
        return $this->_listEnd;
    }

    /**
     * Set Comment for End of Suffix List
     *
     * @param string $listEnd
     */
    protected function setListEnd(string $listEnd): void
    {
        $this->_listEnd = $listEnd;
    }

    /**
     * Get Items to Remove Array
     *
     * @return array
     */
    protected function getListRemove(): array
    {
        return $this->_listRemove;
    }

    /**
     * Set Items to Remove Array
     *
     * @param array $listRemove
     */
    protected function setListRemove(array $listRemove): void
    {
        $this->_listRemove = $listRemove;
    }

    /**
     * Get Substring Between Specified Points
     *
     * @param $haystack
     * @param $start
     * @param $end
     *
     * @return string
     */
    protected function _stringBetween($haystack, $start, $end)
    {
        $startPos = strpos($haystack, $start);
        $startPos += strlen($start);
        $endPos   = strpos($haystack, $end, $startPos) - $startPos;

        return substr($haystack, $startPos, $endPos);
    }

    /**
     * Encode Array Elements with Punycode
     *
     * @param $array
     *
     * @return array
     */
    protected function _arrayPunycode($array)
    {
        $output = [];

        foreach ($array as $key => $value) {
            $output[$key] = idn_to_ascii($value);
        }

        return $output;
    }

    /**
     * Update or Create Parsed Suffix List File
     */
    protected function cacheExtensionList(): void
    {
        $fileName = $this->getCachePath('/domainparser_extensions.json');
        file_put_contents($fileName, json_encode($this->getExtensionList()));
    }

    /**
     * Re-create Suffix List and Cache
     */
    protected function reloadExtensionList(): void
    {
        $startComment = $this->getListStart();
        $endComment   = $this->getListEnd();

        $rawSuffixList = file_get_contents($this->getSuffixUrl());
        $rawSuffixList = $this->_stringBetween($rawSuffixList, $startComment, $endComment);

        $explodedList  = explode("\n", $rawSuffixList);
        $formattedList = [];

        foreach ($explodedList as $key => $value) {
            $value = trim($value);

            foreach ($this->getListRemove() as $remove) {
                if (str_starts_with($value, $remove)) {
                    unset($explodedList[$key]);
                }
            }

            if (empty($value)) {
                unset($explodedList[$key]);
            }
        }

        foreach ($explodedList as $key => $value) {
            $value = idn_to_ascii($value);
            $value = str_replace('*.', '', $value);
            if (str_contains($value, '.')) {
                $levels = explode('.', $value);
                $length = count($levels);
                try {
                    $formattedList[$levels[$length - 1]][] = $value;
                } catch (ErrorException $exception) {
                    $formattedList[$levels[$length - 1]]   = [$levels[$length - 1]];
                    $formattedList[$levels[$length - 1]][] = $value;
                }
            } else {
                $formattedList[$value] = [$value];
            }
        }

        $this->setExtensionList([
            'last_updated' => time(),
            'list'         => $formattedList,
        ]);

        $this->cacheExtensionList();
    }

    /**
     * Re-create Suffix List and Cache
     */
    public function loadExtensionList(): array
    {
        $fileName = $this->getCachePath('/domainparser_extensions.json');

        if ( ! file_exists($fileName)) {
            $this->reloadExtensionList();
        } else {
            $this->setExtensionList(json_decode(file_get_contents($fileName), true));
        }

        if (empty($this->getExtensionList())) {
            $this->reloadExtensionList();
        }
        if (time() - $this->getExtensionList()['last_updated'] > $this->getCacheTime()) {
            $this->reloadExtensionList();
        }

        return $this->getExtensionList();
    }

    /**
     * Format Data for Output
     *
     * @param $output
     *
     * @return false|mixed|string
     */
    protected function formatOutput($output): mixed
    {
        return match ($this->getOutputFormat()) {
            'array' => json_decode(json_encode($output), true),
            'json' => json_encode($output),
            'serialize' => serialize(json_decode(json_encode($output), true)),
            default => json_decode(json_encode($output), false),
        };
    }

    /**
     * Get Valid Domain from Supplied Domain
     *
     * @param $domain
     *
     * @return string
     */
    protected function parseDomain($domain): string
    {
        $url = parse_url($domain);

        $protocol = isset($url['scheme']) ? $url['scheme'] . '://' : 'http://';
        $preparse = $protocol . ($url['host'] ?? $url['path']);
        $parsed   = parse_url($preparse);

        return $parsed['host'];
    }

    /**
     * Return All Custom Domain Parts
     *
     * @param $domain
     * @param $extension
     *
     * @return array
     */
    protected function parseParts($domain, $extension): array
    {
        $domain = str_replace(".{$extension}", '', $domain);
        $levels = explode('.', $domain);
        $name   = end($levels);

        unset($levels[array_search($name, $levels)]);

        return [
            'sub_domains' => empty($levels) ? [] : $levels,
            'domain'      => $name,
        ];
    }

    /**
     * Return Top-level Domain from Supplied Domain
     *
     * @param $domain
     *
     * @return array
     */
    protected function parseExtension($domain): array
    {
        $extensionList = $this->getExtensionList();

        foreach ($extensionList['list'] as $parent => $children) {
            $fragments = explode('.', $domain);

            if (end($fragments) === $parent) {
                $parts = $fragments;
                $loop  = 0;

                foreach ($fragments as $key => $level) {
                    unset($parts[$loop]);
                    $extension = implode('.', $parts);

                    if (in_array($extension, $children)) {
                        return [
                            'group' => $parent,
                            'full'  => $extension,
                        ];
                    }

                    $loop++;
                }
            }
        }

        return [
            'group' => null,
            'full'  => null,
        ];
    }

    /**
     * Validate Hostname
     *
     * @param string $hostname
     *
     * @return bool
     */
    protected function validateHostname(string $hostname): bool
    {
        $hostname  = idn_to_ascii($hostname);
        $fragments = explode('.', $hostname);

        if (strlen($hostname) > 253) {
            return false;
        }

        foreach ($fragments as $key => $value) {
            $length = strlen($value);
            preg_match("/^[a-zA-Z0-9-]+$/s", $value, $matches);

            if ($length > 63) {
                return false;
            }
            if (empty($matches)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validate domain name
     *
     * @param string $domain
     * @param array  $extension
     *
     * @return bool
     */
    protected function validateDomain(string $domain, array $extension): bool
    {
        $domain = idn_to_ascii($domain);

        if ( ! $extension['group'] && ! $extension['full']) {
            return false;
        }

        return $this->validateHostname($domain);
    }

    /**
     * Return Parsed Output of Specified Domain
     *
     * @param $domain
     *
     * @return false|mixed|string
     */
    public function parse($domain): mixed
    {
        $list        = $this->loadExtensionList();
        $fqdn        = $this->parseDomain($domain);
        $extension   = $this->parseExtension($domain);
        $parts       = $this->parseParts($fqdn, $extension['full']);
        $validHost   = $this->validateHostname($fqdn);
        $validDomain = $this->validateDomain($fqdn, $extension);

        return $this->formatOutput([
            'valid_hostname' => $validHost,
            'valid_domain'   => $validDomain,
            'fqdn'           => [
                'ascii' => idn_to_ascii($fqdn),
                'idn'   => $fqdn,
            ],
            'root'           => [
                'ascii' => idn_to_ascii($parts['domain'] . '.' . $extension['full']),
                'idn'   => $parts['domain'] . '.' . $extension['full'],
            ],
            'sub_domains'    => [
                'ascii' => $this->_arrayPunycode($parts['sub_domains']),
                'idn'   => $parts['sub_domains'],
            ],
            'domain'         => [
                'ascii' => idn_to_ascii($parts['domain']),
                'idn'   => $parts['domain'],
            ],
            'extension'      => [
                'group' => [
                    'ascii' => idn_to_ascii($extension['group']),
                    'idn'   => $extension['group'],
                ],
                'full'  => [
                    'ascii' => idn_to_ascii($extension['full']),
                    'idn'   => $extension['full'],
                ],
            ],
        ]);
    }
}
