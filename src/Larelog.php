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
        $data = [
            'direction' => $direction,
            'type' => $type,
            'url' => $url,
            'http_code' => $httpCode,
            'http_method' => $httpMethod,
            'http_protocol_version' => $httpProtocolVersion,
            'request_headers' => $requestHeaders,
            'request' => $request,
            'response_headers' => $responseHeaders,
            'response' => $response,
            'execution_time' => $executionTime,
        ];
        $outputTo = config('larelog.output');
        switch ($outputTo) {
            case self::OUTPUT_DATABASE:
                self::createDbRecord($data);
                break;
            case self::OUTPUT_LOG:
                logger(self::formatLogAsText($data));
                break;
        }
    }

    /**
     * @param array $data
     * @return LarelogLog
     */
    protected static function createDbRecord(array $data): LarelogLog
    {
        $logItem = new LarelogLog();
        $logItem->fill($data);
        $logItem->save();
        return $logItem;
    }

    /**
     * @param array $data
     * @return string
     */
    protected static function formatLogAsText(array $data): string
    {
        $formattedRequestHeaders = self::formatLogHeaders($data['request_headers']);
        $formattedResponseHeaders = self::formatLogHeaders($data['response_headers']);
        $data = array_merge(
            $data,
            [
                'formatted_request_headers' => $formattedRequestHeaders,
                'formatted_response_headers' => $formattedResponseHeaders,
            ]
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
