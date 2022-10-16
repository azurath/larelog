<?php

return [
    /*
     * Logging mode: 'blacklist' or 'whitelist'.
     */
    'mode' => 'blacklist',

    /*
     * Output logs to 'database' (table `larelog_items`), 'log' (you can create new channel and point log to it below) or 'callback'
     */
    'output' => 'database',

    /*
     * Log channel name of 'output' set to 'log'.
     * Don't forget to create channel with that name in 'config/logging.php'.
     */
    'log_channel_name' => 'single',

    /*
     * Callback function name. Array with class name as first element and method name as second.
     * This method will receive LarelogItem model instance as a parameter if new log event occurs.
     * Works only if 'output' set to 'callback'.
     * Example value:
     * [\App\Http\Controllers\TestController::class, 'testCallback']
     */
    'output_callback' => null,

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
     * Example: '\/api\/some\_route\/.*?some\_slug'.
     */
    'blacklist' => [
        //
    ],

    /*
     * Whitelist (works only if mode set to 'whitelist'
     * Regular expressions are supported. Do not forget to escape characters like '/', '_', '?' etc.
     * Example: '\/api\/*'.
     */
    'whitelist' => [
        //
    ],

    /*
     * Max text length.
     * Texts (e.g. requests, responces etc) longer than this value will be truncated.
     */
    'max_field_text_length' => 12 * 1024 * 1024,

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
