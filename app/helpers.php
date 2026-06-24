<?php

if (! function_exists('format_training_minutes')) {
    function format_training_minutes(int $minutes): string
    {
        if ($minutes < 60) {
            return "{$minutes}分";
        }

        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        if ($remainingMinutes === 0) {
            return "{$hours}時間";
        }

        return "{$hours}時間{$remainingMinutes}分";
    }
}
