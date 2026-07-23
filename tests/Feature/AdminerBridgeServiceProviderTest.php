<?php

declare(strict_types=1);

use AdminerBridge\AdminerBridge\AdminerBridge;

it('resolves the singleton', function () {
    expect(app(AdminerBridge::class))->toBeInstanceOf(AdminerBridge::class);
});

it('returns the same instance from the container', function () {
    expect(app(AdminerBridge::class))->toBe(app(AdminerBridge::class));
});
