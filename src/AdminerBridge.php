<?php

declare(strict_types=1);

namespace AdminerBridge\AdminerBridge;

use Composer\InstalledVersions;
use Illuminate\Contracts\Cookie\QueueingFactory;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Http\Request;
use Illuminate\Session\SessionManager;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

class AdminerBridge
{
    public function __construct(
        private readonly SessionManager $sessionManager,
        private readonly AdminerAccessGuard $accessGuard,
        private readonly QueueingFactory $cookieJar,
        private readonly EncryptCookies $encryptCookies,
        private readonly AddQueuedCookiesToResponse $addQueuedCookies,
    ) {}

    /**
     * Hand the request over to the vendored Adminer application.
     *
     * Adminer manages its own session, headers, and output (including calling
     * `exit` on redirects, errors, and asset responses), so this method never
     * builds or returns a Laravel response — it lets Adminer's script own the
     * rest of the request.
     */
    public function serve(): void
    {
        $this->accessGuard->enforceAllowedDriver();
        $this->accessGuard->sanitizeLanguageRequest();

        $handler = $this->sessionManager
            ->driver(config('adminer-bridge.session_driver'))
            ->getHandler();

        session_set_save_handler(new SyncedSessionHandler($handler), false);

        /**
         * Adminer calls exit() directly on every redirect, login attempt, and
         * asset response - which never returns back up through Laravel's
         * middleware, so AddQueuedCookiesToResponse (and EncryptCookies) never
         * get a chance to turn a queued cookie into a real Set-Cookie header.
         * That's silently fatal for the "cookie" session driver, whose writes
         * are nothing but a queued cookie: every login, permanent-login
         * checkbox, and theme switch would appear to work for exactly one
         * request and then evaporate. A shutdown function still runs after
         * exit(), so force the session closed (flushing it to the handler)
         * and hand-emit whatever ended up queued before the process ends.
         */
        register_shutdown_function(function (): void {
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_write_close();
            }

            if (! headers_sent()) {
                foreach ($this->queuedCookieHeaders() as $header) {
                    header($header, false);
                }
            }
        });

        require_once __DIR__.'/adminer-object.php';

        $adminerDirectory = InstalledVersions::getInstallPath('vrana/adminer').'/adminer';
        $previousDirectory = getcwd();

        try {
            chdir($adminerDirectory);

            require $adminerDirectory.'/index.php';
        } finally {
            if ($previousDirectory !== false) {
                chdir($previousDirectory);
            }
        }
    }

    /**
     * Reuse the configured EncryptCookies/AddQueuedCookiesToResponse
     * middleware to render exactly the "Set-Cookie" headers Laravel would
     * have attached had this request finished through the normal response
     * cycle, instead of reimplementing cookie encryption by hand. Only acts
     * on the middleware actually present in the configured route middleware,
     * so a host app that deliberately left one out isn't overridden here.
     *
     * @return list<string>
     */
    public function queuedCookieHeaders(): array
    {
        if ($this->cookieJar->getQueuedCookies() === []) {
            return [];
        }

        $middleware = (array) config('adminer-bridge.route.middleware', []);

        if (! in_array(AddQueuedCookiesToResponse::class, $middleware, true)) {
            return [];
        }

        $next = fn (): Response => $this->addQueuedCookies->handle(Request::create('/'), fn (): Response => new Response);

        $response = in_array(EncryptCookies::class, $middleware, true)
            ? $this->encryptCookies->handle(Request::create('/'), $next)
            : $next();

        return array_values(array_map(
            static fn (Cookie $cookie): string => 'Set-Cookie: '.$cookie,
            $response->headers->getCookies(),
        ));
    }
}
