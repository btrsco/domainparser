<?php

return [
    'output_format' => 'object', // Options: object, array, json, serialize
    'cache'         => [
        'path'      => sys_get_temp_dir(),
        'life_time' => 7 * (24 * 60 * 60), // 7 Days
    ],
    'list'          => [
        'url'    => 'https://publicsuffix.org/list/effective_tld_names.dat',
        'start'  => '// ===BEGIN ICANN DOMAINS===',
        'end'    => '// ===END ICANN DOMAINS===',
        'remove' => ['//', '!'],
    ],
];
