<?php

namespace btrsco\DomainParser;

use TrueBV\Punycode;

class DomainParser
{
    /**
     * Output Format String ('object', 'array', 'json', 'serialize')
     * @var $_outputFormat
     */
    protected $_outputFormat;

    /**
     * Path to System Temp Directory
     * @var $_tempPath
     */
    protected $_tempPath;

    /**
     * Cached File Lifetime in Seconds
     * @var $_cacheTime
     */
    protected $_cacheTime;

    /**
     * List All of Top-Level Domains
     * @var $_tldList
     */
    protected $_tldList = [];

    /**
     * Url of Top-Level Suffix List
     * @var $_suffixUrl
     */
    protected $_suffixUrl;

    public function __construct( $outputFormat = 'object', $options = [] )
    {
        $this->setOutputFormat( $outputFormat );
        $this->setTempPath( $options['temp_path'] ?? sys_get_temp_dir() );
        $this->setCacheTime( $options['life_time'] ?? 604800 ); // 7 Days
        $this->setSuffixUrl( $options['list_url'] ?? 'https://publicsuffix.org/list/effective_tld_names.dat' );
    }

    /**
     * Get Output Format String
     * @return mixed
     */
    protected function getOutputFormat()
    {
        return $this->_outputFormat;
    }

    /**
     * Set Output Format String
     * @param mixed $outputFormat
     */
    protected function setOutputFormat( $outputFormat ): void
    {
        $this->_outputFormat = $outputFormat;
    }

    /**
     * Get System Temp Path
     * @param string $path
     * @return mixed
     */
    protected function getTempPath( $path = '' )
    {
        return rtrim( $this->_tempPath, '/' ) . '/' . ltrim( $path, '/' );
    }

    /**
     * Set System Temp Path
     * @param mixed $tempPath
     */
    protected function setTempPath( $tempPath = null ): void
    {
        $this->_tempPath = $tempPath ?? sys_get_temp_dir();
    }

    /**
     * Get File Cache Life Time
     * @return mixed
     */
    protected function getCacheTime()
    {
        return $this->_cacheTime;
    }

    /**
     * Set File Cache Life Time
     * @param mixed $cacheTime
     */
    protected function setCacheTime( $cacheTime ): void
    {
        $this->_cacheTime = $cacheTime;
    }

    /**
     * Get List of Top Level Suffixes
     * @return mixed
     */
    protected function getTldList()
    {
        return $this->_tldList;
    }

    /**
     * Set List of Top Level Suffixes
     * @param mixed $tldList
     */
    protected function setTldList( $tldList ): void
    {
        $this->_tldList = $tldList;
    }

    /**
     * Get Url of Top-Level Suffix List
     * @return mixed
     */
    protected function getSuffixUrl()
    {
        return $this->_suffixUrl;
    }

    /**
     * Set Url of Top-Level Suffix List
     * @param mixed $suffixUrl
     */
    protected function setSuffixUrl( $suffixUrl ): void
    {
        $this->_suffixUrl = $suffixUrl;
    }

    /**
     * Check if String Starts with Substring
     * @param $haystack
     * @param $needle
     * @return bool
     */
    protected function _startsWith( $haystack, $needle )
    {
        return substr( $haystack, 0, strlen( $needle ) ) === $needle;
    }

    /**
     * Check if String Contains Substring
     * @param $haystack
     * @param $needle
     * @return bool
     */
    protected function _stringContains( $haystack, $needle )
    {
        return strpos( $haystack, $needle ) !== false;
    }


    /**
     * Get Substring Between Specified Points
     * @param $haystack
     * @param $start
     * @param $end
     * @return string
     */
    protected function _stringBetween( $haystack, $start, $end )
    {
        $startPos  = strpos( $haystack, $start );
        $startPos += strlen( $start );
        $endPos    = strpos( $haystack, $end, $startPos ) - $startPos;
        return substr( $haystack, $startPos, $endPos );
    }

    /**
     * Encode Array Elements with Punycode
     * @param $array
     * @return array
     */
    protected function _arrayPunycode( $array )
    {
        $punycode = new Punycode();
        $output = [];

        foreach ( $array as $key => $value )
        {
            $output[$key] = $punycode->encode( $value );
        }

        return $output;
    }

    /**
     * Update or Create Parsed Suffix List File
     */
    protected function cacheTldList()
    {
        $fileName = $this->getTempPath( '/domainparser_tlds.json' );
        file_put_contents( $fileName, json_encode( $this->getTldList() ) );
    }

    /**
     * Re-create Suffix List and Cache
     */
    protected function reloadTldList()
    {
        $startComment  = '// ===BEGIN ICANN DOMAINS===';
        $endComment    = '// ===END ICANN DOMAINS===';

        $rawSuffixList = file_get_contents( $this->getSuffixUrl() );
        $rawSuffixList = $this->_stringBetween( $rawSuffixList, $startComment, $endComment );

        $explodedList  = explode( "\n", $rawSuffixList );
        $formattedList = [];

        $punycode      = new Punycode();

        foreach ( $explodedList as $key => $value )
        {
            $value = trim( $value );

            if ( $this->_startsWith( $value, '//' ) ) unset( $explodedList[$key] );
            if ( $this->_startsWith( $value, '!' ) ) unset( $explodedList[$key] );
            if ( empty( $value ) ) unset( $explodedList[$key] );
        }

        foreach ( $explodedList as $key => $value )
        {
            $value = $punycode->encode( $value );
            $value = str_replace( '*.', '', $value );

            if ( $this->_stringContains( $value, '.' ) ) {
                $levels = explode( '.', $value );
                $length = count( $levels );
                try {
                    array_push( $formattedList[ $levels[ $length - 1 ] ], $value );
                } catch ( \ErrorException $exception ) {
                    $formattedList[ $levels[ $length - 1 ] ] = [ $levels[ $length - 1 ] ];
                    array_push( $formattedList[ $levels[ $length - 1 ] ], $value );
                }
            } else {
                $formattedList[$value] = [$value];
            }
        }

        $this->setTldList([
            'last_updated' => time(),
            'list' => $formattedList,
        ]);

        $this->cacheTldList();
    }

    /**
     * Re-create Suffix List and Cache
     */
    protected function loadTldList()
    {
        $fileName = $this->getTempPath( '/domainparser_tlds.json' );

        if ( !file_exists( $fileName ) ) $this->reloadTldList();
        else $this->setTldList( json_decode( file_get_contents( $fileName ), true ) );

        if ( empty( $this->getTldList() ) ) $this->reloadTldList();
        if ( time() - $this->getTldList()['last_updated'] > $this->getCacheTime() ) $this->reloadTldList();

        return $this->getTldList();
    }

    /**
     * Format Data for Output
     * @param $output
     * @return false|mixed|string
     */
    protected function formatOutput( $output )
    {
        switch ( $this->getOutputFormat() ) {
            case 'array':
                return json_decode( json_encode( $output ), true );
                break;
            case 'json':
                return json_encode( $output );
                break;
            case 'serialize':
                return serialize( json_decode( json_encode( $output ), true ) );
                break;
            default:
                return json_decode( json_encode( $output ), false );
                break;
        }
    }

    /**
     * Get Valid Domain from Supplied Domain
     * @param $domain
     * @return string
     */
    protected function parseDomain( $domain )
    {
        $url = parse_url( $domain );

        $protocol = isset( $url['scheme'] ) ? $url['scheme'] . '://' : 'http://';
        $preparse = $protocol . ( $url['host'] ?? $url['path'] );
        $parsed   = parse_url( $preparse );

        return $parsed['host'];
    }

    /**
     * Return All Custom Domain Parts
     * @param $domain
     * @param $tld
     * @return array
     */
    protected function parseParts( $domain, $tld )
    {
        $domain = str_replace( $tld, '', $domain );
        $domain = trim( $domain, '.' );
        $levels = explode( '.', $domain );
        $name   = end( $levels );

        unset( $levels[ array_search( $name, $levels ) ] );

        return [
            'sub_domains' => empty( $levels ) ? [] : $levels,
            'domain' => $name
        ];
    }

    /**
     * Return Top-level Domain from Supplied Domain
     * @param $domain
     * @return false|mixed|string
     */
    protected function parseTld( $domain )
    {
        $tldList = $this->getTldList();

        foreach ( $tldList['list'] as $parent => $children )
        {
            $fragments = explode( '.', $domain );

            if ( end( $fragments ) === $parent ) {
                $parts = $fragments;
                $loop  = 0;

                foreach ( $fragments as $key => $level )
                {
                    unset( $parts[$loop] );
                    $tld = implode( '.', $parts );

                    if ( in_array( $tld, $children ) ) {
                        return [
                            'group' => $parent,
                            'tld' => $tld
                        ];
                    }

                    $loop++;
                }
            }
        }

        return [
            'group' => null,
            'tld' => null,
        ];
    }

    /**
     * Return Parsed Output of Specified Domain
     * @param $domain
     * @return false|mixed|string
     * TODO: Add Domain Name Validator
     */
    public function parse( $domain )
    {
        $list  = $this->loadTldList();
        $fqdn  = $this->parseDomain( $domain );
        $tld   = $this->parseTld( $domain );
        $parts = $this->parseParts( $fqdn, $tld['tld'] );

        $puny  = new Punycode();

        return $this->formatOutput([
            'fqdn' => [
                'ascii' => $puny->encode( $fqdn ),
                'idn'   => $fqdn
            ],
            'sub_domains' => [
                'ascii' => $this->_arrayPunycode( $parts['sub_domains'] ),
                'idn'   => $parts['sub_domains']
            ],
            'domain' => [
                'ascii' => $puny->encode( $parts['domain'] ),
                'idn'   => $parts['domain']
            ],
            'tld' => [
                'group' => [
                    'ascii' => $puny->encode( $tld['group'] ),
                    'idn'   => $tld['group']
                ],
                'tld' => [
                    'ascii' => $puny->encode( $tld['tld'] ),
                    'idn'   => $tld['tld']
                ]
            ]
        ]);
    }
}
