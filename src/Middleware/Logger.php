<?php

namespace Azurath\Larelog\Middleware;

use Azurath\Larelog\Larelog;
use Closure;
use Azurath\Larelog\Utils\Utils;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @property Larelog $larelog
 * @property Utils $utils
 */
class Logger
{
    protected $larelog;

    public function __construct()
    {
        $this->larelog = new Larelog();
    }

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return void
     * @throws Exception
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        $this->larelog->logIncomingRequest($request, $response);
        return $response;
    }

}
