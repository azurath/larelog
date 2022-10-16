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

    /* Database Log rotation settings
     * Log rotation deletes oldest 50% of existing logs.
     */

    /* Rotate logs? */
    'database_log_rotation' => true,

    /* Minimum disk space percent to perform cleanup */
    'min_free_disk_space_percent_to_clean' => 20,

    /* Minimum database log entries count to perform cleanup by one of conditions */
    'min_database_log_entries_to_clean' => 2 * 10 * 1000,

    /* Maximum database log entries before cleanup by entries count condition */
    'max_database_log_entries' => 1000 * 1000,
];
