<?php

namespace Azurath\Larelog\Middleware;

use Azurath\Larelog\Larelog;
use Closure;
use Azurath\Larelog\Utils\Utils;
use Exception;
use Illuminate\Http\Request;

/**
 * @property Larelog $larelog
 * @property Utils $utils
 */
class Logger
{
    protected $utils;
    protected $larelog;

    public function __construct()
    {
        $this->utils = new Utils();
        $this->larelog = new Larelog();
    }

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     * @throws Exception
     */
    public function handle(Request $request, Closure $next)
    {
        $utils = new Utils();
        $utils->start();
        $response = $next($request);
        $executionTime = $utils->end();
        $requestUri = $request->getUri();
        $direction = Larelog::REQUEST_DIRECTION_INCOMING;
        $type = $this->larelog->getIncomingRequestType($request);
        if ($this->larelog->shouldLog($requestUri, $direction, $type)) {
            $this->larelog->log(
                $direction,
                $type,
                $requestUri,
                $response->status(),
                $request->getMethod(),
                $request->getProtocolVersion(),
                json_encode($request->headers->all()),
                $request->getContent(),
                json_encode($response->headers->all()),
                $response->getContent(),
                $executionTime
            );
        }

        return $response;
    }

}
