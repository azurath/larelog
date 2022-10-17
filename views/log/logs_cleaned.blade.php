@php
    use \Azurath\Larelog\LarelogRotateLogs as LRL;
@endphp
HTTP Logs Cleaned.
    * Deleted {{ $stats[LRL::STATS_TOTAL_CLEANED_COUNT] }} of {{ $stats[LRL::STATS_TOTAL_COUNT] }} log items ({{ $stats[LRL::STATS_TOTAL_COUNT_LEFT] }} records left).
@foreach($stats[LRL::STATS_METHODS] as $methodName => $method)
        * Deleted by {{$methodName}}: {{ $method[LRL::STATS_METHODS_CLEANED_COUNT] }} of {{ $method[LRL::STATS_METHODS_COUNT] }} ({{ $method[LRL::STATS_LOGS_COUNT_LEFT] }} records left).
@endforeach
    * Cleanup time: {{ number_format($stats[LRL::STATS_CLEANUP_TIME], 2) }} seconds.
