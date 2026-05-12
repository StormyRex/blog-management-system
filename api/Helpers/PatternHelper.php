<?php

namespace Shive\BlogManagementSystem\Helpers;

class PatternHelper
{
    public static function email(): string
    {
        return '/^[^\s@]+@[^\s@]+\.[^\s@]+$/';
    }

    public static function username(): string
    {
        return '/^[a-z0-9_]{3,30}$/';
    }

    public static function password(): string
    {
        return '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@#$_])[A-Za-z\d@#$_]{8,16}$/';
    }

    public static function slug(): string
    {
        return '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';
    }

    public static function phone(): string
    {
        return '/^[0-9]{10,15}$/';
    }
}
