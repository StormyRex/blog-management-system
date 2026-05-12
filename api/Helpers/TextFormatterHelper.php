<?php

namespace Shive\BlogManagementSystem\Helpers;

class TextFormatterHelper
{
    public static function limit(
        string $text,
        int $length = 100
    ): string {

        if (strlen($text) <= $length) {
            return $text;
        }

        return substr($text, 0, $length) . '...';
    }

    public static function excerpt(
        string $text,
        int $length = 120
    ): string {

        $cleanText = strip_tags($text);

        return self::limit($cleanText, $length);
    }

    public static function capitalize(
        string $text
    ): string {

        $text = strtolower($text);

        return ucfirst($text);
    }

    public static function title(
        string $text
    ): string {

        $text = strtolower($text);

        return ucwords($text);
    }

    public static function sanitize(
        string $text
    ): string {

        return htmlspecialchars(
            $text,
            ENT_QUOTES,
            'UTF-8'
        );
    }

    public static function nl2brSafe(
        string $text
    ): string {

        return nl2br(
            self::sanitize($text)
        );
    }
}
