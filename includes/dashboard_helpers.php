<?php
/**
 * Helpers khusus dashboard agar halaman statistik tetap aman saat database
 * masih kosong atau hasil query tidak memiliki data numerik.
 */
function dashboard_max(array $values, float $fallback = 1.0): float
{
    $numbers = [];
    foreach ($values as $value) {
        if (is_numeric($value)) {
            $numbers[] = (float) $value;
        }
    }

    if (!$numbers) {
        return $fallback;
    }

    return max($fallback, max($numbers));
}

function dashboard_percent(float $value, float $max, float $minimum = 0): float
{
    if ($max <= 0) {
        return $minimum;
    }

    $percent = ($value / $max) * 100;
    return max($minimum, min(100, $percent));
}

function dashboard_date_label(?string $value, string $format = 'd-m-Y H:i'): string
{
    if (!$value) {
        return '-';
    }

    $timestamp = strtotime($value);
    return $timestamp === false ? '-' : date($format, $timestamp);
}
