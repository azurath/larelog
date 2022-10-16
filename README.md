Laravel Request Logger.

TL;DR:
1. Run `composer require azurath/larelog`
2. Add `\Azurath\Larelog\Middleware\Logger::class` to `$middleware` in `app/Http/Kernel.php`
3. Run `php artisan vendor:publish --provider=Azurath\Larelog\LarelogProvider`
4. Run `php artisan migrate`
5. Enjoy.

If you're using GuzzleHttp and want to log requests perfomed via it:
```
    $stack = (new Larelog())->getGuzzleLoggerStack();
    $client = new Client([
        'handler' => $stack,
    ]);
    //your code goes here
```

Settings stored in `config/larelog.php`.

