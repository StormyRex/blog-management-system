<?php

namespace Shive\BlogManagementSystem\Helpers;

class PatternHelper
{
    private static array $patterns = [
        'email' => '/^[^\s@]+@[^\s@]+\.[^\s@]+$/',
        'username' => '/^[a-z0-9_]{3,30}$/',
        'password' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@#$_])[A-Za-z\d@#$_]{8,16}$/',
        'slug' => '/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
        'phone' => '/^[0-9]{10,15}$/'
    ];

    public static function match(string $key, string $value): bool
    {
        if (!isset(self::$patterns[$key])) {
            return false;
        }

        return preg_match(self::$patterns[$key], $value) === 1;
    }

    public static function email(): string
    {
        return self::$patterns['email'];
    }

    public static function username(): string
    {
        return self::$patterns['username'];
    }

    public static function password(): string
    {
        return self::$patterns['password'];
    }

    public static function slug(): string
    {
        return self::$patterns['slug'];
    }

    public static function phone(): string
    {
        return self::$patterns['phone'];
    }
}
