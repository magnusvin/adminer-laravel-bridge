<?php

declare(strict_types=1);

use Adminer\Adminer;
use AdminerBridge\AdminerBridge\AdminerBridgeAdminer;

/**
 * An override whose signature stops matching the parent is loud - PHP refuses
 * to load the class and every test fails. An override whose method upstream
 * simply *removed* is silent: it stays a perfectly valid method that nothing
 * ever calls, and the behaviour it was there to provide disappears without a
 * single failure. `verifyVersion()` gating Adminer's outbound version check is
 * the clearest example - losing it would quietly start phoning home again.
 *
 * Releases here are cut automatically when Adminer publishes, so this is the
 * guard that keeps a green build from meaning less than it looks like.
 */
it('overrides only methods that still exist on Adminer', function () {
    // Adminer is a plain include, not an autoloaded package class.
    require_once __DIR__.'/../../vendor/vrana/adminer/adminer/include/adminer.inc.php';

    $parent = new ReflectionClass(Adminer::class);

    $overrides = array_values(array_filter(
        (new ReflectionClass(AdminerBridgeAdminer::class))->getMethods(ReflectionMethod::IS_PUBLIC),
        static fn (ReflectionMethod $method): bool => $method->class === AdminerBridgeAdminer::class
            && ! $method->isConstructor(),
    ));

    expect($overrides)->not->toBeEmpty();

    foreach ($overrides as $method) {
        expect($parent->hasMethod($method->getName()))->toBeTrue(
            "AdminerBridgeAdminer::{$method->getName()}() no longer overrides anything on ".Adminer::class
            .' - upstream removed or renamed it, so this method is now dead code and whatever it was'
            .' gating has silently reverted to Adminer default behaviour.',
        );
    }
});
