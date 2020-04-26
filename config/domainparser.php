<?php

return [
    'cache' > [
        'path' => sys_get_temp_dir(),
        'life_time' => 604800 // 7 Days
    ],
    'list' => [
        'url' => 'https://publicsuffix.org/list/effective_tld_names.dat',
        'start' => '// ===BEGIN ICANN DOMAINS===',
        'end' => '/// ===END ICANN DOMAINS===',
        'remove' => ['//', '!']
    ]
];
