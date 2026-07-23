<?php

declare(strict_types=1);

use AdminerBridge\AdminerBridge\SyncedSessionHandler;
use Illuminate\Http\Request;
use Illuminate\Session\CookieSessionHandler;

/**
 * CookieSessionHandler stores the session payload under a cookie literally
 * named after the raw session id, with no namespace - unlike every other
 * handler, where the id is only ever an internal lookup key a browser never
 * sees. SyncedSessionHandler appends "-adminer" to that one visible cookie
 * name to avoid colliding with an unrelated cookie a host app's own
 * guard/auth stack happens to set.
 */
it('namespaces the id with -adminer for the cookie session handler', function () {
    $cookieHandler = new CookieSessionHandler(app('cookie'), 120);
    $cookieHandler->setRequest(Request::create('/'));

    (new SyncedSessionHandler($cookieHandler))->write('abc123', 'session-payload');

    $names = array_map(
        fn ($cookie) => $cookie->getName(),
        app('cookie')->getQueuedCookies(),
    );

    expect($names)->toBe(['abc123-adminer']);
});

it('leaves the id untouched for handlers other than the cookie session handler', function () {
    $fake = new class implements SessionHandlerInterface
    {
        /** @var array<string, string> */
        public array $store = [];

        public function open(string $path, string $name): bool
        {
            return true;
        }

        public function close(): bool
        {
            return true;
        }

        public function read(string $id): string|false
        {
            return $this->store[$id] ?? '';
        }

        public function write(string $id, string $data): bool
        {
            $this->store[$id] = $data;

            return true;
        }

        public function destroy(string $id): bool
        {
            unset($this->store[$id]);

            return true;
        }

        public function gc(int $max_lifetime): int|false
        {
            return 0;
        }
    };

    (new SyncedSessionHandler($fake))->write('abc123', 'session-payload');

    expect($fake->store)->toBe(['abc123' => 'session-payload']);
});
