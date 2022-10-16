<?php

namespace Azurath\Larelog;

use Azurath\Larelog\Models\LarelogLog;
use Azurath\Larelog\Utils\Utils;
use Exception;
use GuzzleHttp\HandlerStack;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Larelog
{
    const MAX_TEXT_LENGTH = 12 * 1024 * 1024;

    const LOG_TYPE_GUZZLE_HTTP = 'guzzlehttp';
    const LOG_TYPE_UNKNOWN = 'unknown';

    const REQUEST_DIRECTION_INCOMING = 'incoming';
    const REQUEST_DIRECTION_OUTGOING = 'outgoing';

    const MODE_BLACKLIST = 'blacklist';
    const MODE_WHITELIST = 'whitelist';

    const OUTPUT_DATABASE = 'database';
    const OUTPUT_LOG = 'log';
    const OUTPUT_CALLBACK

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
    public static function log(
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
            case self::OUTPUT_DATABASE:
                $logItem->save();
                break;
            case self::OUTPUT_LOG:
                Utils::logData($logItem->formatAsText());
                break;
            default:
                throw new Exception('Unknown log output method: ' . $outputTo);
        }
    }

    /**
     * @param array $data
     * @return LarelogLog
     */
    protected static function createLogItem(array $data): LarelogLog
    {
        $logItem = new LarelogLog();
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

    public function getIncomingRequestType(Request $request): ?string
    {
        $route = $request->route();
        $requestRouteMiddlewares = $route ? $route->getAction('middleware') : null;
        return !empty($requestRouteMiddlewares) ? $requestRouteMiddlewares[0] : self::LOG_TYPE_UNKNOWN;
    }

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

    protected function truncateText(string $text): string
    {
        return mb_substr($text, 0, self::MAX_TEXT_LENGTH);
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

    protected function shouldLogByDirection(?string $direction): bool
    {
        return in_array($direction, config('larelog.directions'));
    }

    protected function shouldLogByType(?string $type): bool
    {
        return (empty($type) || in_array($type, config('larelog.types')));
    }

    /**
     * @throws Exception
     */
    protected function isUriInList(string $uri, array $list): bool
    {
        $subpattern = implode('|', $list);
        $pattern = '/(' . $subpattern . ')/';
        try {
            $result = preg_match($pattern, $uri);
        } catch (Exception $e) {
            throw new Exception('Regexp error: ' . $e->getMessage() . '. Regex: ' . $pattern);
        }
        return $result !== 0;
    }
}
