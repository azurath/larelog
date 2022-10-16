# Laravel Request Logger.

## Logs all HTTP requests performed to your Laravel application, as well as GuzzleHTTP requests.

### Installation
1. Run `composer require azurath/larelog`
2. Add `\Azurath\Larelog\Middleware\Logger::class` to the end of `$middleware` array in `app/Http/Kernel.php`
3. Run `php artisan vendor:publish --provider=Azurath\Larelog\LarelogProvider`
4. Run `php artisan migrate`
5. Enjoy.

### GuzzleHttp
If you're using GuzzleHttp and want to log requests perfomed via it, just pass addinional parameter while creating GuzzleHttp instance:
```
    $client = new \GuzzleHttp\Client([
        'handler' => (new \Azurath\Larelog\Larelog())->getGuzzleLoggerStack(),
    ]);
```

### Settings
Settings stored in `config/larelog.php`.

### Log rotation
Add `$schedule->job(new \Azurath\Larelog\LarelogRotateLogs())->hourly();` to `app/Console/Kernel.php` to enable log rotation.

### Convert request item to text
You can get text representation of log item stored in database:
```
$logItem = \Azurath\Larelog\Models\LarelogLog::where(...)->first();
$text = $logItem->formatAsText();
```
### Get authenticated user for the request item
If request performed by authenticated user, it's id and model name (for cases when you have different auth guards with different providers) also saved to db.
You can access this user:
```
$logItem = \Azurath\Larelog\Models\LarelogLog::where(...)->first();
$user = $logItem->user;
```

### Fields
Basically this logger saves request start time (`started_at`), request execution time (`execution_time`), direction of request (`direction`, 'incoming' or 'outgoing'), laravel request type (`type`, api/web/etc), HTTP method (`http_method`), HTTP protocol version (`http_protocol_version`), HTTP response code (`http_code`), URL (`url`), request headers (`request_headers`), request data (`request`), response headers(`response_headers`), response data (`response`), and, if authenticated, Laravel user who're performed this request (property `user`, relation `user()`).
