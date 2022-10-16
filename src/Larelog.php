<?php

namespace Azurath\Larelog;

use Azurath\Larelog\Models\LarelogLog;
use Azurath\Larelog\Utils\Utils;
use Exception;
use GuzzleHttp\HandlerStack;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Larelog
{
    const MAX_TEXT_LENGTH = 12 * 1024 * 1024;

    const LOG_TYPE_GUZZLE_HTTP = 'guzzlehttp';

    const REQUEST_DIRECTION_INCOMING = 'incoming';
    const REQUEST_DIRECTION_OUTGOING = 'outgoing';

    const MODE_BLACKLIST = 'blacklist';
    const MODE_WHITELIST = 'whitelist';

    const OUTPUT_DATABASE = 'database';
    const OUTPUT_LOG = 'log';

    /**
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
     * @return void
     */
    public static function log(
        ?string $direction,
        ?string $type,
        string  $url,
        string  $httpCode,
        string  $httpMethod,
        string  $httpProtocolVersion,
        string  $requestHeaders,
        string  $request,
        string  $responseHeaders,
        string  $response,
        ?float  $executionTime = null
    )
    {
        $output = config('larelog.output');
        switch ($output) {
            case self::OUTPUT_DATABASE:
                self::createDbRecord(...func_get_args());
                break;
            case self::OUTPUT_LOG:
                logger(self::formatLogAsText(...func_get_args()));
                break;
        }
    }

    /**
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
     * @return LarelogLog
     */
    protected static function createDbRecord(
        ?string $direction,
        ?string $type,
        string  $url,
        string  $httpCode,
        string  $httpMethod,
        string  $httpProtocolVersion,
        string  $requestHeaders,
        string  $request,
        string  $responseHeaders,
        string  $response,
        ?float  $executionTime = null
    ): LarelogLog {
        $logItem = new LarelogLog();
        $logItem->direction = $direction;
        $logItem->type = $type;
        $logItem->url = $url;
        $logItem->http_code = $httpCode;
        $logItem->http_method = $httpMethod;
        $logItem->http_protocol_version = $httpProtocolVersion;
        $logItem->request_headers = $requestHeaders;
        $logItem->request = $request;
        $logItem->response_headers = $responseHeaders;
        $logItem->response = $response;
        $logItem->execution_time = $executionTime;
        $logItem->save();
        return $logItem;
    }

    /**
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
     * @return string
     */
    protected static function formatLogAsText(
        ?string $direction,
        ?string $type,
        string  $url,
        string  $httpCode,
        string  $httpMethod,
        string  $httpProtocolVersion,
        string  $requestHeaders,
        string  $request,
        string  $responseHeaders,
        string  $response,
        ?float  $executionTime = null
    ): string
    {
        $formattedRequestHeaders = self::formatLogHeaders($requestHeaders);
        $formattedResponseHeaders = self::formatLogHeaders($responseHeaders);
        $data = compact(
            'direction',
            'type',
            'url',
            'httpCode',
            'httpMethod',
            'httpProtocolVersion',
            'formattedRequestHeaders',
            'request',
            'formattedResponseHeaders',
            'response',
            'executionTime'
        );
        return View::make('larelog::log.log', $data)->render();
    }

    /**
     * @param string $data
     * @return string
     */
    protected static function formatLogHeaders(string $data): string
    {
        $valuesOnly = false;
        if (!$data) {
            return '';
        }
        $decodedData = json_decode($data, true);
        if (!$decodedData) {
            $decodedData = explode(PHP_EOL, $data);
            $valuesOnly = true;
        }
        if ($decodedData === $data) {
            return $data;
        }
        $resultData = [];

        foreach ($decodedData as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $subvalue) {
                    $resultData[] = "\t" . $key . ': ' . $subvalue;
                }
            } else {
                $resultData[] = "\t" . ($valuesOnly ? $value : $key . ': ' . $value);
            }
        }
        return implode(PHP_EOL, $resultData);
    }

    public function getIncomingRequestType(Request $request): ?string
    {
        $requestRouteMiddlewares = $request->route()->getAction('middleware');
        return !empty($requestRouteMiddlewares) ? $requestRouteMiddlewares[0] : null;
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
                        if ($this->shouldLog($requestUri, $direction, $type)) {
                            $this->log(
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
                                $executionTime
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
                return in_array($direction, config('larelog.directions'))
                    && in_array($type, config('larelog.types'))
                    && !$this->isUriInList($uri, $list, $direction, $type);
            case self::MODE_WHITELIST:
                $list = config('larelog.whitelist');
                return in_array($direction, config('larelog.directions'))
                    && in_array($type, config('larelog.types'))
                    && $this->isUriInList($uri, $list, $direction, $type);
            default:
                throw new Exception('Unknown mode: ' . $mode);
        }
    }

    /**
     * @throws Exception
     */
    protected function isUriInList(string $uri, array $list, ?string $direction, ?string $type): bool
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
