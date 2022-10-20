<?php

namespace Azurath\Larelog\Utils;

use Exception;
use Illuminate\Support\Facades\Log;

/**
 *
 */
class Utils
{

    protected $startTime;

    /**
     * @return float
     */
    public function start(): float
    {
        $this->startTime = $this->getMicroTime();
        return $this->startTime;
    }

    public function getStartTime(): ?float
    {
        return $this->startTime;
    }

    /**
     * @return float|null
     */
    public function end(): ?float
    {
        return $this->startTime ? $this->getMicroTime() - $this->startTime : null;
    }

    public function getMicroTime(): ?float
    {
        return microtime(true);
    }

    /**
     * @param int|null $size
     * @param int $precision
     * @return string|null
     */
    public static function formatBytes(?int $size, int $precision = 2): ?string
    {
        if ($size > 0) {
            $size = (int)$size;
            $base = log($size) / log(1024);
            $suffixes = [' bytes', ' KB', ' MB', ' GB', ' TB'];

            return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
        } else {
            return $size;
        }
    }

    /**
     * @param $data
     * @param string|null $channel
     * @return void
     */
    public static function logData($data, ?string $channel = null): void
    {
        $logChannel = $channel ?: config('logging.default');
        Log::channel($logChannel)->info($data);
    }

    /**
     * @param array|null $callback
     * @param mixed ...$args
     * @return void
     * @throws Exception
     */
    public function callCallback(?array $callback, ...$args): void
    {
        if (!empty($callback) && sizeof($callback) === 2 && method_exists($callback[0], $callback[1])) {
            $callback(...$args);
        } else {
            $callbackAsText = !empty($callback) ? implode(', ', $callback) : json_encode($callback);
            throw new Exception('Callback function not found. Trying to call: [' . $callbackAsText . ']');
        }
    }

}
