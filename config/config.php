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

    /*
     * Directions: 'incoming' or 'outgoing'
     */
    'directions' => [
        'incoming',
        'outgoing',
    ],

    /*
     * Types: 'web', 'api', 'guzzlehttp'
     */
    'types' => [
        'web',
        'api',
        'guzzlehttp',
    ],

    /*
     * Blacklist (works only if mode set to 'blacklist')
     */
    'blacklist' => [
        '\/api\/some\_route\/.*?some\_slug',
    ],

    /*
     * Whitelist (works only if mode set to 'whitelist'
     */
    'whitelist' => [
        '\/api\/*',
    ],

    /* Settings below currently not supported */
    'database_log_rotation' => true,
    'min_disk_space_percent_to_clean' => 20,
    'min_database_log_entries_to_clean' => 2 * 10 * 1000,
    'max_database_log_entries' => 1000 * 1000,
];
