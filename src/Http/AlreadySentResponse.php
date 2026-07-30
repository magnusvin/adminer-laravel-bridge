<?php

declare(strict_types=1);

namespace AdminerBridge\AdminerBridge\Http;

use Illuminate\Http\Response;

/**
 * Adminer's own script has already written real headers and body content
 * directly to output by the time AdminerBridge::serve() returns for a normal
 * page render (it only calls exit() itself on redirects, logins, and asset
 * responses). Once that happens, PHP can no longer send any header for this
 * response either way - but the base Response::sendHeaders() still tries to
 * override the status line via header() in that case, which is what raises
 * PHP's "headers already sent" warning. Skipping it here is a no-op, not a
 * behavior change: nothing it would have sent could have reached the client.
 */
final class AlreadySentResponse extends Response
{
    public function sendHeaders(?int $statusCode = null): static
    {
        return $this;
    }
}
