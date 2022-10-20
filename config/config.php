<?php

return [
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
     * [\App\Http\Controllers\TestController::class, 'logEntryCallback']
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
     * URL filtering mode: 'blacklist' or 'whitelist'.
     */
    'url_filter_mode' => 'blacklist',

    /*
     * URLs list to filter.
     * Requests matches that URL patterns will be white- or blacklisted, depends on 'url_filter_mode' value.
     * Regular expressions are supported. Do not forget to escape characters like '/', '_', '?' etc.
     * Case sensitive.
     * Example: '\/api\/some\_route\/.*?some\_slug'.
     */
    'url_list' => [
        //
    ],

    /*
     * MIME filtering mode: 'blacklist' or 'whitelist'.
     */
    'mime_filter_mode' => 'blacklist',

    /*
     * MIMEs list to filter.
     * Requests with that MIMEs (in request or response headers) will be white- or blacklisted, depends on 'mime_filter_mode' value.
     * Case insensitive.
     * Example: 'application/json'
     */
    'mime_list' => [
        //
    ],

    /*
     * HTTP status code filtering mode: 'blacklist' or 'whitelist'.
     */
    'http_status_code_filter_mode' => 'blacklist',

    /*
     * HTTP status codes list to filter.
     * Requests with these status codes will be white- or blacklisted, depends on 'http_status_code_filter_mode' value.
     * Example: '404', '500'
     */
    'http_status_code_list' => [
        //
    ],

    /*
     * Max text length for 'request' and 'response' data in database.
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
    */

    /*
     * Rotate database logs?
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
     * Default TTL is 30 days.
     */
    'log_entry_ttl' => 60 * 60 * 24 * 30,

    /*
     * Maximum database log entries before cleanup by entries count condition.
     * Set to boolean false to disable.
     */
    'max_database_log_entries' => 250 * 1000,

    /*
     * Callback that will be called after successful cleanup (if something cleaned),
     * null to disable. It will receive stats array as a parameter.
     * Example value:
     * [\App\Http\Controllers\TestController::class, 'callbackAfterCleanup']
     */
    'callback_after_cleanup' => null,
];
