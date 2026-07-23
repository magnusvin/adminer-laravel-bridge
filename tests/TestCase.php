<?php

declare(strict_types=1);

namespace AdminerBridge\AdminerBridge\Tests;

use AdminerBridge\AdminerBridge\AdminerBridgeServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            AdminerBridgeServiceProvider::class,
        ];
    }
}
