<?php

declare(strict_types=1);

namespace AdminerBridge\AdminerBridge;

use Illuminate\Session\CookieSessionHandler;
use Illuminate\Session\ExistenceAwareInterface;
use SessionHandlerInterface;

/**
 * Wraps a Laravel session handler for use with PHP's native session_set_save_handler().
 *
 * Illuminate\Session\DatabaseSessionHandler decides INSERT vs UPDATE in write() from a
 * private $exists flag, but that flag is only ever set to *true* (by read(), when a row
 * is found) - it's never reset back to false, because Laravel's own Store always builds
 * a brand new handler instance per request and explicitly calls setExists(false) itself
 * whenever *it* regenerates the session id (see Illuminate\Session\Store::regenerate()
 * and the ExistenceAwareInterface it checks for).
 *
 * Adminer calls the native session_regenerate_id() on every login attempt (session
 * fixation defence) instead of going through Laravel's Store, so nothing ever resets
 * that flag. Once it's true for one id, it stays true for every id written afterwards in
 * the same request, so write() for a freshly generated, never-persisted id silently
 * issues an UPDATE against a row that doesn't exist - the new session is discarded and
 * the next request sees "Session expired, please login again." Resetting the flag before
 * every read() lets read() recompute it correctly for whichever id is actually current.
 */
final class SyncedSessionHandler implements SessionHandlerInterface
{
    public function __construct(private readonly SessionHandlerInterface $handler) {}

    public function open(string $path, string $name): bool
    {
        return $this->handler->open($path, $name);
    }

    public function close(): bool
    {
        return $this->handler->close();
    }

    public function read(string $id): string|false
    {
        if ($this->handler instanceof ExistenceAwareInterface) {
            $this->handler->setExists(false);
        }

        return $this->handler->read($this->namespacedId($id));
    }

    public function write(string $id, string $data): bool
    {
        $this->read($id);

        return $this->handler->write($this->namespacedId($id), $data);
    }

    public function destroy(string $id): bool
    {
        return $this->handler->destroy($this->namespacedId($id));
    }

    public function gc(int $max_lifetime): int|false
    {
        return $this->handler->gc($max_lifetime);
    }

    /**
     * Illuminate\Session\CookieSessionHandler stores the session payload
     * under a cookie named after the raw session id, with no namespace of
     * its own - unlike every other handler, where $id is only ever an
     * internal lookup key (file name, database row, cache key) a browser
     * never sees. That makes it the one case where this id doubles as a
     * visible cookie name, with some chance of colliding with an unrelated
     * cookie a host app's own guard/auth stack happens to set.
     */
    private function namespacedId(string $id): string
    {
        return $this->handler instanceof CookieSessionHandler ? $id.'-adminer' : $id;
    }
}
