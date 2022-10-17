# *La*ravel *Re*quest *Log*ger.

## Logs all HTTP requests performed to and from your Laravel application, as well as GuzzleHTTP requests.

### Features: 
+ Log incoming (Laravel) and outgoing (GuzzleHttp) requests
+ Blacklists and whitelists for URLs with regular expressions
+ Save logs to database
+ Write logs to Laravel log
+ Pass log items to user callback function
+ Log rotation (by disk space, count and date)
+ Save authenticated user (multiple laravel user models are supported)

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
Or you can attach Larelog callback to existing stack:
```
    $handlerStack = HandlerStack::create();
    //...
    $larelogStackCallback = (new \Azurath\Larelog\Larelog())->getGuzzleLoggerStackItem();
    $handlerStack->push($larelogStackCallback);    
```

### Settings
Settings stored in `config/larelog.php`.

### Log rotation
Add `$schedule->job(new \Azurath\Larelog\LarelogRotateLogs())->hourly();` to `app/Console/Kernel.php` to enable log rotation.

### Convert request item to text
You can get text representation of log item stored in database:
```
$logItem = \Azurath\Larelog\Models\LarelogItem::where(...)->first();
$text = $logItem->formatAsText();
```
### Get authenticated user for the request item
If request performed by authenticated user, it's id and model name (for cases when you have different auth guards with different providers) also saved to db.
You can access this user:
```
$logItem = \Azurath\Larelog\Models\LarelogItem::where(...)->first();
$user = $logItem->user;
```
### Fields
Basically this logger saves request start time (`started_at`), request execution time (`execution_time`), direction of request (`direction`, 'incoming' or 'outgoing'), laravel request type (`type`, api/web/etc), HTTP method (`http_method`), HTTP protocol version (`http_protocol_version`), HTTP response code (`http_code`), URL (`url`), request headers (`request_headers`), request data (`request`), response headers(`response_headers`), response data (`response`), and, if authenticated, Laravel user who're performed this request (property `user`, relation `user()`).

### FAQ
**Q**: Why 'Azurath'?\
**A**: It's misheard name 'Azurewrath', which then spoked like 'Azuraf', which become a nickname spoken like 'Azurat' over a years.

**Q**: Why 'Larelog'?\
**A**: **LA**ravel **RE**quest **LOG**ger.
