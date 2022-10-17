<?php

return [
    /*
     * Logging mode: 'blacklist' or 'whitelist'.
     */
    'mode' => 'blacklist',

    /*
     * Output logs to:
     * - 'database' (table `larelog_items`)
     * - 'log' (you can create new log channel and point log to it below)
     * - 'callback' (you can pass method which will be called for each captured log item)
     */
    'output' => 'database',

    /*
     * Log channel name (when 'output' set to 'log').
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
     * Max text length for 'request' and 'response' data.
     * Texts (e.g. requests, responses etc.) longer than this value will be truncated.
     * MySQL, by default, has 'max_allowed_packet' set to 16 Mb.
     * 'request' and 'response' fields in database has LONGBLOB type, which allows store up to 4 Gb of data.
     * If you need to store huge request data in database (more than 7Mb per request/response),
     * you should adjust 'max_allowed_packet' value at mysql server settings.
     * before changing setting below.
     */
    'max_field_text_length' => 7 * 1024 * 1024,

    /*
     * Database Log rotation settings
     * Log rotation deletes oldest 50% of existing logs.
    */

    /*
     * Rotate logs?
    */
    'database_log_rotation' => true,

    /*
     * Minimum database log entries count to perform cleanup by any matched condition
     */
    'min_database_log_entries_to_clean' => 2 * 10 * 1000,

    /*
     * Log entry time to leave (seconds).
     * This is priority setting. If logs are cleaned by TTL, then next conditions will match remained logs count.
     * Any log item older than given value will be deleted on next cleanup.
     * Set to boolean false to disable.
     */
    'log_entry_ttl' => 60 * 60 * 24,

    /*
     * Minimum free disk space percent to perform cleanup.
     * Set to boolean false to disable.
     */
    'min_free_disk_space_percent_to_clean' => 20,

    /*
     * Maximum database log entries before cleanup by entries count condition.
     * Set to boolean false to disable.
     */
    'max_database_log_entries' => 1000 * 1000,
];
