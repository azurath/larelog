<?php

namespace Azurath\Larelog;

use Azurath\Larelog\Models\LarelogItem;
use Azurath\Larelog\Utils\Utils;
use Exception;
use Illuminate\Support\Facades\View;

class LarelogRotateLogs
{
    protected const TOTAL_HDD_SPACE = 'total';
    protected const FREE_SPACE_BEFORE = 'free-before';
    protected const FREE_SPACE_AFTER = 'free-after';
    protected const TOTAL_COUNT = 'total-count';
    protected const CLEANED_COUNT = 'cleaned-count';

    protected $cleanedStats;

    /**
     * @throws Exception
     */
    public function cleanLogs()
    {
        $count = LarelogItem::query()->count();
        if ($this->shouldClean($count)) {
            $this->fillStatsBefore();
            $countToDelete = $this->getCountToDelete($count);
            $deleteToRecord = LarelogItem::query()
                ->orderBy('id', 'asc')
                ->skip($countToDelete)
                ->take(1)
                ->first();
            LarelogItem::where('id', '<', $deleteToRecord->id)
                ->delete();
            $this->fillStatsAfter($count);
            Larelog::printToLog($this->logCleanupStats($countToDelete));
        }
    }

    /**
     * @param int $count
     * @return int
     */
    protected function getCountToDelete(int $count): int
    {
        return floor($count / 2);
    }

    /**
     * @return void
     */
    protected function fillStatsBefore(): void
    {
        $this->cleanedStats[self::TOTAL_HDD_SPACE] = Utils::formatBytes(disk_total_space('/'));
        $this->cleanedStats[self::FREE_SPACE_BEFORE] = Utils::formatBytes(disk_free_space('/'));
    }

    /**
     * @param int $count
     * @return void
     */
    protected function fillStatsAfter(int $count): void
    {
        $this->cleanedStats[self::FREE_SPACE_AFTER] = Utils::formatBytes(disk_free_space('/'));
        $this->cleanedStats[self::TOTAL_COUNT] = $count;
        $this->cleanedStats[self::CLEANED_COUNT] = $this->getCountToDelete($count);
    }

    /**
     * @param int $count
     * @return bool
     */
    protected function shouldClean(int $count): bool
    {
        return config('larelog.database_log_rotation')
            && $count >= config('larelog.min_database_log_entries_to_clean')
            && (
                $this->shouldCleanByLogsCount($count)
                || $this->shouldCleanByDiskSpace()
            );
    }

    /**
     * @return int
     */
    protected function getFreeSpacePercent(): int
    {
        $totalSpace = disk_total_space('/');
        $freeSpace = disk_free_space('/');
        return ceil($freeSpace / $totalSpace * 100);
    }

    /**
     * @return bool
     */
    protected function shouldCleanByDiskSpace(): bool
    {
        return $this->getFreeSpacePercent() <= config('larelog.min_free_disk_space_percent_to_clean');
    }

    /**
     * @param int $count
     * @return bool
     */
    protected function shouldCleanByLogsCount(int $count): bool
    {
        return $count >= config('larelog.max_database_log_entries');
    }

    protected function logCleanupStats(int $cleanedCount): string
    {
        return View::make('larelog::log.logs_cleaned')
            ->with([
                'cleanedCount' => $cleanedCount,
                'cleanedStats' => $this->cleanedStats,
            ]);
    }
}
