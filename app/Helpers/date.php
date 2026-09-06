<?php

use Carbon\CarbonInterface;

if (! function_exists('format_date')) {
    /**
     * Centralized date display format: Chinese style YYYY-MM-DD.
     * Optionally keeps a HH:MM:SS time portion after the date.
     *
     * @param  Carbon\CarbonInterface|string|null  $date
     */
    function format_date($date, bool $withTime = false): string
    {
        if (empty($date)) {
            return '-';
        }

        $carbon = $date instanceof CarbonInterface ? $date : Carbon\Carbon::parse($date);

        return $carbon->format($withTime ? 'Y-m-d H:i:s' : 'Y-m-d');
    }
}
