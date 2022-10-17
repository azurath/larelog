<?php

namespace Azurath\Larelog;

use Azurath\Larelog\Models\LarelogItem;
use Azurath\Larelog\Utils\Utils;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
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

    protected const METHOD_TTL = 'ttl';
    protected const METHOD_DISK_SPACE = 'disk-space';
    protected const METHOD_COUNT = 'count';

    protected $cleanedStats;
    protected $initialLogsCount;
    protected $utils;

    public function __construct()
    {
        $this->utils = new Utils();
        $this->initialLogsCount = $this->getLogsCount();
        $this->cleanedStats = [];
        $this->cleanedStats[self::STATS_METHODS] = [];
        $this->cleanedStats[self::STATS_TOTAL_COUNT] = $this->initialLogsCount;
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
            $this->cleanLogsByTTL();
            $this->cleanLogsByCount();
            $this->cleanLogsByDiskSpace();
            if ($this->isSomethingCleaned()) {
                $this->storeCleanupTime($this->utils->end());
                Larelog::printToLog($this->logCleanupStats());
            }
        }
    }

    protected function getLogsCount(): int
    {
        return LarelogItem::query()
            ->count();
    }

    /**
     * @param int $count
     * @return int
     */
    protected function getCountToDelete(int $count): int
    {
        return floor($count / 2);
    }


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

    protected function isSomethingCleaned(): bool
    {
        return !!$this->cleanedStats[self::STATS_TOTAL_CLEANED_COUNT];
    }

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
            && $this->initialLogsCount >= config('larelog.min_database_log_entries_to_clean');
    }

    /**
     * @return int
     */
    protected function getFreeSpacePercent(): int
    {
        $totalSpace = disk_total_space('/');
        $freeSpace = disk_free_space('/');
        return $totalSpace ? ceil($freeSpace / $totalSpace * 100) : 100;
    }

    /**
     * @return bool
     */
    protected function shouldCleanByDiskSpace(): bool
    {
        $minFreeDiskSpacePercent = config('larelog.min_free_disk_space_percent_to_clean');
        return $minFreeDiskSpacePercent !== false
            && $this->getFreeSpacePercent() <= $minFreeDiskSpacePercent;
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

    protected function shouldCleanByTTL(int $threshold): bool
    {
        return !!$this->getThresholdQuery($threshold)
            ->selectRaw('1')
            ->skip(0)
            ->take(1)
            ->get()
            ->count();
    }

    protected function logCleanupStats(): string
    {
        return View::make('larelog::log.logs_cleaned')
            ->with([
                'stats' => $this->cleanedStats,
            ]);
    }

    protected function deleteHalfPartOfLogs(int $count): int
    {
        $countToDelete = $this->getCountToDelete($count);
        $deleteToRecord = LarelogItem::query()
            ->orderBy('id')
            ->skip($countToDelete)
            ->take(1)
            ->first();
        return $deleteToRecord
            ? LarelogItem::query()
                ->where('id', '<', $deleteToRecord->id)
                ->delete()
            : 0;
    }

    protected function cleanLogsByTTL(): void
    {
        $threshold = config('larelog.log_entry_ttl');
        if ($threshold !== false && $this->shouldCleanByTTL($threshold)) {
            $count = $this->getLogsCount();
            $cleanedCount = $this->getThresholdQuery($threshold)
                ->delete();
            $this->pushResultToStats(self::METHOD_TTL, $count, $cleanedCount);
        }
    }

    protected function getThresholdQuery(int $threshold): Builder
    {
        $toDate = Carbon::now()->subSeconds($threshold);
        return LarelogItem::query()
            ->where('created_at', '<=', $toDate);
    }

    protected function cleanLogsByCount(): void
    {
        $count = $this->getLogsCount();
        if ($this->shouldCleanByLogsCount($count)) {
            $cleanedCount = $this->deleteHalfPartOfLogs($count);
            $this->pushResultToStats(self::METHOD_COUNT, $count, $cleanedCount);
        }
    }

    protected function cleanLogsByDiskSpace(): void
    {
        if ($this->shouldCleanByDiskSpace()) {
            $count = $this->getLogsCount();
            $cleanedCount = $this->deleteHalfPartOfLogs($count);
            if ($cleanedCount) {
                $this->pushResultToStats(self::METHOD_DISK_SPACE, $count, $cleanedCount);
            }
        }
    }

}
