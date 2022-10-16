<?php

namespace Azurath\Larelog;

class Constants {

    public const REQUEST_DIRECTION_INCOMING = 'incoming';
    public const MODE_BLACKLIST = 'blacklist';
    public const REQUEST_DIRECTION_OUTGOING = 'outgoing';
    public const OUTPUT_TO_CALLBACK = 'callback';
    public const LOG_TYPE_API = 'api';
    public const OUTPUT_TO_LOG = 'log';
    public const OUTPUT_TO_DATABASE = 'database';
    public const LOG_TYPES = [
        Constants::LOG_TYPE_UNKNOWN => 'Unknown',
        Constants::LOG_TYPE_WEB => 'Web',
        Constants::LOG_TYPE_API => 'Api',
        Constants::LOG_TYPE_GUZZLE_HTTP => 'Guzzle HTTP',
    ];
    public const MODE_WHITELIST = 'whitelist';
    public const LOG_TYPE_WEB = 'web';
    public const LOG_TYPE_UNKNOWN = 'unknown';
    public const LOG_TYPE_GUZZLE_HTTP = 'guzzlehttp';
    public const REQUEST_DIRECTIONS = [
        Constants::REQUEST_DIRECTION_INCOMING => 'Incoming',
        Constants::REQUEST_DIRECTION_OUTGOING => 'Outgoing',
    ];
}
