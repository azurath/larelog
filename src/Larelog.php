<?php

namespace Azurath\Larelog;

use Azurath\Larelog\Models\LarelogItem;
use Azurath\Larelog\Utils\Utils;
use Closure;
use Exception;
use GuzzleHttp\HandlerStack;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;

class Larelog
{
    const LOG_TYPE_GUZZLE_HTTP = 'guzzlehttp';
    const LOG_TYPE_UNKNOWN = 'unknown';

    const REQUEST_DIRECTION_INCOMING = 'incoming';
    const REQUEST_DIRECTION_OUTGOING = 'outgoing';

    const MODE_BLACKLIST = 'blacklist';
    const MODE_WHITELIST = 'whitelist';

    const OUTPUT_TO_DATABASE = 'database';
    const OUTPUT_TO_LOG = 'log';
    const OUTPUT_TO_CALLBACK = 'callback';

    protected $utils;

    /**
     *
     */
    function __construct()
    {
        $this->utils = new Utils();
    }

    /**
     * @param float|null $startTime
     * @param string|null $direction
     * @param string|null $type
     * @param string $url
     * @param string $httpCode
     * @param string $httpMethod
     * @param string $httpProtocolVersion
     * @param string $requestHeaders
     * @param string $request
     * @param string $responseHeaders
     * @param string $response
     * @param float|null $executionTime
     * @param mixed $user
     * @return void
     * @throws Exception
     */
    public function log(
        ?float          $startTime,
        ?string         $direction,
        ?string         $type,
        string          $url,
        string          $httpCode,
        string          $httpMethod,
        string          $httpProtocolVersion,
        string          $requestHeaders,
        string          $request,
        string          $responseHeaders,
        string          $response,
        ?float          $executionTime = null,
        Authenticatable $user = null
    )
    {
        $data = [
            'started_at' => $startTime,
            'direction' => $direction,
            'type' => $type ?? self::LOG_TYPE_UNKNOWN,
            'url' => $url,
            'http_code' => $httpCode,
            'http_method' => $httpMethod,
            'http_protocol_version' => $httpProtocolVersion,
            'request_headers' => $requestHeaders,
            'request' => $request,
            'response_headers' => $responseHeaders,
            'response' => $response,
            'execution_time' => $executionTime,
            'user_model' => $user ? get_class($user) : null,
            'user_id' => $user ? $user->id : null,
        ];

        $logItem = self::createLogItem($data);

        $outputTo = config('larelog.output');
        switch ($outputTo) {
            case self::OUTPUT_TO_DATABASE:
                $logItem->save();
                break;
            case self::OUTPUT_TO_LOG:
                self::printToLog($logItem->formatAsText());
                break;
            case self::OUTPUT_TO_CALLBACK:
                $this->outputToCallback($logItem);
                break;
            default:
                throw new Exception('Unknown log output method: ' . $outputTo);
        }
    }

    /**
     * @param string $text
     * @return void
     */
    public static function printToLog(string $text): void
    {
        Utils::logData($text, config('larelog.log_channel_name'));
    }

    /**
     * @param array $data
     * @return LarelogItem
     */
    protected static function createLogItem(array $data): LarelogItem
    {
        $logItem = new LarelogItem();
        $logItem->fill($data);
        return $logItem;
    }

    /**
     * @param string $headers
     * @return string
     */
    public static function formatLogHeaders(string $headers): string
    {
        $valuesOnly = false;
        if (!$headers) {
            return '';
        }
        $decodedData = json_decode($headers, true);
        if (!$decodedData) {
            $decodedData = explode(PHP_EOL, $headers);
            $valuesOnly = true;
        }
        if ($decodedData === $headers) {
            return $headers;
        }
        $resultData = [];

        foreach ($decodedData as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $subValue) {
                    $resultData[] = "\t" . $key . ': ' . $subValue;
                }
            } else {
                $resultData[] = "\t" . ($valuesOnly ? $value : $key . ': ' . $value);
            }
        }
        return implode(PHP_EOL, $resultData);
    }

    /**
     * @param Request $request
     * @return string|null
     */
    public function getIncomingRequestType(Request $request): ?string
    {
        $route = $request->route();
        $requestRouteMiddlewares = $route ? $route->getAction('middleware') : null;
        return !empty($requestRouteMiddlewares) ? $requestRouteMiddlewares[0] : self::LOG_TYPE_UNKNOWN;
    }

    /**
     * @param Request $request
     * @param Closure $next
     * @return Response
     * @throws Exception
     */
    public function logIncomingRequest(Request $request, Closure $next): Response
    {
        $utils = new Utils();
        $utils->start();
        $response = $next($request);
        $executionTime = $utils->end();
        $requestUri = $request->getUri();
        $direction = Larelog::REQUEST_DIRECTION_INCOMING;
        $type = $this->getIncomingRequestType($request);
        $user = Auth::user();
        if ($this->shouldLog($requestUri, $direction, $type)) {
            $this->log(
                $utils->getStartTime(),
                $direction,
                $type,
                $requestUri,
                $response->getStatusCode(),
                $request->getMethod(),
                $request->getProtocolVersion(),
                json_encode($request->headers->all()),
                $this->truncateText($request->getContent()),
                json_encode($response->headers->all()),
                $this->truncateText($response->getContent()),
                $executionTime,
                $user
            );
        }

        return $response;
    }

    /**
     * @return HandlerStack
     */
    public function getGuzzleLoggerStack(): HandlerStack
    {
        $stack = HandlerStack::create();
        $stack->push(function (callable $handler) {
            return function (RequestInterface $request, array $options) use ($handler) {
                $utils = new Utils();
                $utils->start();
                return $handler($request, $options)->then(
                    function (ResponseInterface $response) use ($request, $utils) {
                        $executionTime = $utils->end();
                        $requestUri = $request->getUri();
                        $direction = self::REQUEST_DIRECTION_OUTGOING;
                        $type = self::LOG_TYPE_GUZZLE_HTTP;
                        $user = Auth::user();
                        if ($this->shouldLog($requestUri, $direction, $type)) {
                            $this->log(
                                $utils->getStartTime(),
                                $direction,
                                $type,
                                $requestUri,
                                $response->getStatusCode(),
                                $request->getMethod(),
                                'HTTP/' . $response->getProtocolVersion(),
                                json_encode($request->getHeaders()),
                                $this->truncateText($request->getBody()),
                                json_encode($response->getHeaders()),
                                $this->truncateText($response->getBody()),
                                $executionTime,
                                $user
                            );
                        }
                        return $response;
                    }
                );
            };
        });
        return $stack;
    }

    /**
     * @param string $text
     * @return string
     */
    protected function truncateText(string $text): string
    {
        return mb_substr($text, 0, config('larelog.max_field_text_length'));
    }

    /**
     * @throws Exception
     */
    public function shouldLog(string $uri, ?string $direction, ?string $type): bool
    {
        $mode = config('larelog.mode');
        switch ($mode) {
            case self::MODE_BLACKLIST:
                $list = config('larelog.blacklist');
                $shouldLogByList = !$this->isUriInList($uri, $list);
                break;
            case self::MODE_WHITELIST:
                $list = config('larelog.whitelist');
                $shouldLogByList = $this->isUriInList($uri, $list);
                break;
            default:
                throw new Exception('Unknown mode: ' . $mode);
        }
        return $this->shouldLogByDirection($direction)
            && $this->shouldLogByType($type)
            && $shouldLogByList;
    }

    /**
     * @param string|null $direction
     * @return bool
     */
    protected function shouldLogByDirection(?string $direction): bool
    {
        return in_array($direction, config('larelog.directions'));
    }

    /**
     * @param string|null $type
     * @return bool
     */
    protected function shouldLogByType(?string $type): bool
    {
        return (empty($type) || in_array($type, config('larelog.types')));
    }


    /**
     * @param string $uri
     * @param array $list
     * @return bool
     * @throws Exception
     */
    protected function isUriInList(string $uri, array $list): bool
    {
        if (!empty($list)) {
            $subpattern = implode('|', $list);
            $pattern = '/(' . $subpattern . ')/';
            try {
                $result = preg_match($pattern, $uri);
            } catch (Exception $e) {
                throw new Exception('Regexp error: ' . $e->getMessage() . '. Expression: ' . $pattern);
            }
            return $result !== 0;
        } else {
            return false;
        }
    }


    /**
     * @param LarelogItem $logItem
     * @return void
     * @throws Exception
     */
    protected function outputToCallback(LarelogItem $logItem): void
    {
        $callback = config('larelog.output_callback');
        if (!empty($callback) && sizeof($callback) === 2 && method_exists($callback[0], $callback[1])) {
            $callback($logItem);
        } else {
            $callbackAsText = !empty($callback) ? implode(', ', $callback) : json_encode($callback);
            throw new Exception('Output callback ain\'t set or function doesn\'t exists. Trying to call: [' . $callbackAsText . ']');
        }
    }
}
