<?php

return [
    /*
     * Logging mode: 'blacklist' or 'whitelist'.
     */
    'mode' => 'blacklist',

    /*
     * Output logs to 'database', 'log' or 'callback'
    */
    'output' => 'database',

    /*
     * What request directions we should log: 'incoming', 'outgoing'
     */
    'directions' => [
        'incoming',
        'outgoing',
    ],

    /*
     * What request types we should log: 'web', 'api', 'guzzlehttp', 'unknown'
     * 'unknown' type basically applied for incoming requests that do not match any route.
     */
    'types' => [
        'web',
        'api',
        'guzzlehttp',
        'unknown',
    ],

    /*
     * Blacklist (works only if mode set to 'blacklist')
     * Regular expressions are supported. Do not forget to escape characters like '/', '_', '?' etc.
     */
    'blacklist' => [
        '\/api\/some\_route\/.*?some\_slug',
    ],

    /*
     * Whitelist (works only if mode set to 'whitelist'
     * Regular expressions are supported. Do not forget to escape characters like '/', '_', '?' etc.
     */
    'whitelist' => [
        '\/api\/*',
    ],

    /*
     * Database Log rotation settings
     * Log rotation deletes oldest 50% of existing logs.
    */

    /* Rotate logs? */
    'database_log_rotation' => true,

    /* Minimum free disk space percent to perform cleanup */
    'min_free_disk_space_percent_to_clean' => 20,

    /* Minimum database log entries count to perform cleanup by one of conditions */
    'min_database_log_entries_to_clean' => 2 * 10 * 1000,

    /* Maximum database log entries before cleanup by entries count condition */
    'max_database_log_entries' => 1000 * 1000,
];
