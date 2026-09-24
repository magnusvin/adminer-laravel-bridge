<?php

declare(strict_types=1);

use AdminerBridge\AdminerBridge\Tests\TestCase;

// Adminer's index.php defines this when it boots; tests that exercise the
// Adminer subclass directly include its sources without going through that
// entry point, so they have to define it themselves - the same escape hatch
// Adminer's own test suite uses. "./" is the value index.php picks, making
// every asset link relative to the served directory.
if (! defined('Adminer\\DIR')) {
    define('Adminer\\DIR', './');
}

uses(TestCase::class)->in(__DIR__);
