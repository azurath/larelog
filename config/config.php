<?php

return [
    /*
     * Mode: 'blacklist' or 'whitelist'.
     */
    'mode' => 'blacklist',

    /*
     * Output to 'database' or 'log'
    */
    'output' => 'database',

    'directions' => [
        'incoming',
        'outgoing',
    ],

    'types' => [
        'web',
        'api',
        'guzzlehttp',
    ],

    'blacklist' => [
        '\/api\/fake\_route\/.*?data',
    ],

    'whitelist' => [
        '\/api\/*',
    ],
];
