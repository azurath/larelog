<?php

namespace Azurath\Larelog\Utils;

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
        $this->startTime = microtime(true);
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
        return $this->startTime ? microtime(true) - $this->startTime : null;
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

}
