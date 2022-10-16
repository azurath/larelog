<?php

namespace Azurath\Larelog\Middleware;

use Azurath\Larelog\Larelog;
use Closure;
use Azurath\Larelog\Utils\Utils;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

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
     * @return Response
     * @throws Exception
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $this->larelog->logIncomingRequest($request, $next);
    }

}
