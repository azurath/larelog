Laravel Request Logger.
Logs all HTTP requests performed to your Laravel application, as well as GuzzleHTTP requests.

TL;DR:
1. Run `composer require azurath/larelog`
2. Add `\Azurath\Larelog\Middleware\Logger::class` to `$middleware` in `app/Http/Kernel.php`
3. Run `php artisan vendor:publish --provider=Azurath\Larelog\LarelogProvider`
4. Run `php artisan migrate`
5. Enjoy.

If you're using GuzzleHttp and want to log requests perfomed via it:
```
    $stack = (new \Azurath\Larelog\Larelog())->getGuzzleLoggerStack();
    $client = new \GuzzleHttp\Client([
        'handler' => $stack,
    ]);
    //your code goes here
```

Settings stored in `config/larelog.php`.

Add `$schedule->job(new \Azurath\Larelog\LarelogRotateLogs())->hourly();` to `app/Console/Kernel.php` to enable log rotation.

You can get text representation of log item stored in database:
`$logItem = \Azurath\Larelog\Models\LarelogLog::first()->formatAsText()`
