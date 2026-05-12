<?php

namespace Shive\BlogManagementSystem\Helpers;

class DateFormatterHelper
{
    public static function format(
        string $date,
        string $format = 'M d, Y'
    ): string {

        $timestamp = strtotime($date);

        if ($timestamp === false) {
            return '';
        }

        return date($format, $timestamp);
    }

    public static function timeAgo(string $datetime): string
    {
        $timestamp = strtotime($datetime);

        if ($timestamp === false) {
            return '';
        }

        $diff = time() - $timestamp;

        if ($diff < 60) {
            return 'just now';
        }

        if ($diff < 3600) {
            $minutes = floor($diff / 60);
            return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
        }

        if ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
        }

        if ($diff < 172800) {
            return 'yesterday';
        }

        if ($diff < 2592000) {
            $days = floor($diff / 86400);
            return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
        }

        if ($diff < 31536000) {
            $months = floor($diff / 2592000);
            return $months . ' month' . ($months > 1 ? 's' : '') . ' ago';
        }

        $years = floor($diff / 31536000);
        return $years . ' year' . ($years > 1 ? 's' : '') . ' ago';
    }

    public static function now(): string
    {
        return date('Y-m-d H:i:s');
    }

    public static function isToday(string $date): bool
    {
        $timestamp = strtotime($date);

        if ($timestamp === false) {
            return false;
        }

        return date('Y-m-d', $timestamp) === date('Y-m-d');
    }
}
