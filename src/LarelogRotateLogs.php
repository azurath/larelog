<?php

namespace Azurath\Larelog;

use Azurath\Larelog\Models\LarelogItem;
use Azurath\Larelog\Utils\Utils;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

class LarelogRotateLogs
{
    public const STATS_METHODS = 'methods';
    public const STATS_METHODS_COUNT = 'count';
    public const STATS_METHODS_CLEANED_COUNT = 'deleted';
    public const STATS_TOTAL_COUNT = 'total_count';
    public const STATS_TOTAL_CLEANED_COUNT = 'total_cleaned_count';
    public const STATS_TOTAL_COUNT_LEFT = 'total_count_left';
    public const STATS_LOGS_COUNT_LEFT = 'logs_count_left';
    public const STATS_CLEANUP_TIME = 'cleanup_time';

    public const METHOD_TTL = 'ttl';
    public const METHOD_COUNT = 'count';

    /**
     * @var array
     */
    protected $cleanedStats;
    /**
     * @var
     */
    protected $logsCount;
    /**
     * @var Utils
     */
    protected $utils;

    /**
     *
     * @throws Exception
     */
    public function __construct()
    {
        $this->utils = new Utils();
        $this->setLogsCount($this->getLogsCountFromDb());
        $this->cleanedStats = [];
        $this->cleanedStats[self::STATS_METHODS] = [];
        $this->cleanedStats[self::STATS_TOTAL_COUNT] = $this->getLogsCount();
        $this->cleanedStats[self::STATS_TOTAL_CLEANED_COUNT] = 0;
        $this->cleanedStats[self::STATS_TOTAL_COUNT_LEFT] = 0;
    }

    /**
     * @throws Exception
     */
    public function cleanLogs()
    {
        if ($this->shouldClean()) {
            $this->utils->start();
            $cleanedCount = $this->cleanLogsByTTL();
            $this->setLogsCount($this->getLogsCount() - $cleanedCount);
            $cleanedCount = $this->cleanLogsByCount();
            $this->setLogsCount($this->getLogsCount() - $cleanedCount);
            if ($this->isSomethingCleaned()) {
                $this->storeCleanupTime($this->utils->end());
                Larelog::printToLog($this->logCleanupStats());
                $this->callbackAfterSuccessfulCleanup();
            }
        }
    }

    /**
     * @return int|null
     */
    protected function getLogsCount(): ?int
    {
        return $this->logsCount;
    }

    /**
     * @param int $logsCount
     * @return void
     */
    protected function setLogsCount(int $logsCount): void
    {
        $this->logsCount = $logsCount;
    }

    /**
     * @return int|null
     * @throws Exception
     */
    protected function getLogsCountFromDb(): ?int
    {
        return LarelogItem::query()
            ->count('id');
    }

    /**
     * @param string $method
     * @param int $count
     * @param int $cleanedCount
     * @return void
     */
    protected function pushResultToStats(string $method, int $count, int $cleanedCount): void
    {
        $this->cleanedStats[self::STATS_METHODS][$method] = [
            self::STATS_METHODS_COUNT => $count,
            self::STATS_METHODS_CLEANED_COUNT => $cleanedCount,
            self::STATS_LOGS_COUNT_LEFT => $count - $cleanedCount,
        ];
        $this->cleanedStats[self::STATS_TOTAL_CLEANED_COUNT] += $cleanedCount;
        $this->cleanedStats[self::STATS_TOTAL_COUNT_LEFT] = $count - $cleanedCount;
    }

    /**
     * @return bool
     */
    protected function isSomethingCleaned(): bool
    {
        return !!$this->cleanedStats[self::STATS_TOTAL_CLEANED_COUNT];
    }

    /**
     * @param float $time
     * @return void
     */
    protected function storeCleanupTime(float $time): void
    {
        $this->cleanedStats[self::STATS_CLEANUP_TIME] = $time;
    }

    /**
     * @return bool
     */
    protected function shouldClean(): bool
    {
        return config('larelog.database_log_rotation')
            && $this->logsCount >= config('larelog.min_database_log_entries_to_clean');
    }

    /**
     * @param int $count
     * @return bool
     */
    protected function shouldCleanByLogsCount(int $count): bool
    {
        $maxDatabaseLogEntries = config('larelog.max_database_log_entries');
        return $maxDatabaseLogEntries !== false
            && $count >= config('larelog.max_database_log_entries');
    }

    /**
     * @param int $threshold
     * @return bool
     */
    protected function shouldCleanByTTL(int $threshold): bool
    {
        return !!$this->getThresholdQuery($threshold)
            ->selectRaw('1')
            ->skip(0)
            ->take(1)
            ->get()
            ->count();
    }

    /**
     * @return string
     */
    protected function logCleanupStats(): string
    {
        return View::make('larelog::log.logs_cleaned')
            ->with([
                'stats' => $this->cleanedStats,
            ]);
    }

    /**
     * @param int $countToDelete
     * @return int
     */
    protected function deleteOldestLogs(int $countToDelete): int
    {
        return LarelogItem::query()
            ->orderBy('id')
            ->take($countToDelete)
            ->delete();
    }

    /**
     * @return int
     */
    protected function cleanLogsByTTL(): int
    {
        $threshold = config('larelog.log_entry_ttl');
        $cleanedCount = 0;
        if ($threshold !== false && $this->shouldCleanByTTL($threshold)) {
            $count = $this->getLogsCount();
            $cleanedCount = $this->getThresholdQuery($threshold)
                ->delete();
            $this->pushResultToStats(self::METHOD_TTL, $count, $cleanedCount);
        }
        return $cleanedCount;
    }

    /**
     * @param int $threshold
     * @return Builder
     */
    protected function getThresholdQuery(int $threshold): Builder
    {
        $toDate = Carbon::now()->subSeconds($threshold);
        return LarelogItem::query()
            ->select('id')
            ->where('created_at', '<=', $toDate);
    }

    /**
     * @return int
     */
    protected function cleanLogsByCount(): int
    {
        $count = $this->getLogsCount();
        $allowedCount = config('larelog.max_database_log_entries');
        $cleanedCount = 0;
        if ($this->shouldCleanByLogsCount($count)) {
            $cleanedCount = $this->deleteOldestLogs($count - $allowedCount);
            $this->pushResultToStats(self::METHOD_COUNT, $count, $cleanedCount);
        }
        return $cleanedCount;
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function callbackAfterSuccessfulCleanup(): void
    {
        $callback = config('larelog.callback_after_cleanup');
        $this->utils->callCallback($callback, $this->cleanedStats);
    }

}
