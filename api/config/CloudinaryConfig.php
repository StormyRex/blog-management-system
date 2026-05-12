<?php

namespace Shive\BlogManagementSystem\Config;

use Dotenv\Dotenv;

class CloudinaryConfig
{
    public static function load(): void
    {
        $dotenv = Dotenv::createImmutable(
            __DIR__ . '/../../'
        );

        $dotenv->load();
    }
}