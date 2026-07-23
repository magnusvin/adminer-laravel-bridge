<?php

declare(strict_types=1);

namespace AdminerBridge\AdminerBridge;

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AdminerAccessGuard
{
    /**
     * Adminer's own query keys for each driver. MySQL/MariaDB ("server") has
     * no dedicated *trigger* - driver.inc.php activates it whenever none of
     * the other four keys are present - but Adminer still uses "server" as
     * the query key that carries the hostname once it's active, so a request
     * carrying it is still a real signal that mysql is the driver in play.
     */
    private const array DRIVER_QUERY_KEYS = ['server', 'pgsql', 'sqlite', 'oracle', 'mssql'];

    public function __construct(private readonly Request $request) {}

    /**
     * Reject the request before Adminer boots if it targets a database driver
     * outside the configured allow-list. Adminer decides which driver to
     * activate from either the posted login field or a query key matching the
     * driver id, so both have to be checked here to actually enforce anything.
     *
     * A request carrying neither signal (the bare login page, before any
     * driver has actually been chosen) is left alone: driver.inc.php's own
     * fallback-to-mysql only kicks in once Adminer actually tries to connect,
     * so treating every first visit as an implicit mysql attempt would block
     * the login page itself whenever mysql isn't in the allow-list.
     */
    public function enforceAllowedDriver(): void
    {
        $allowed = config('adminer-bridge.drivers');

        if (! is_array($allowed)) {
            return;
        }

        $requested = $this->request->input('auth.driver');

        if (! is_string($requested)) {
            $requested = null;

            foreach (self::DRIVER_QUERY_KEYS as $key) {
                if ($this->request->query->has($key)) {
                    $requested = $key;

                    break;
                }
            }
        }

        if ($requested === null) {
            return;
        }

        if (! in_array($requested, $allowed, true)) {
            throw new HttpException(403, 'This database driver is not allowed.');
        }
    }

    /**
     * Strip any requested/stored language outside the configured allow-list
     * before Adminer resolves LANG, so the resolution chain (posted value,
     * cookie, session, then Accept-Language) can't land on a disallowed one.
     * LANG is defined as a constant during Adminer's boot, so this has to run
     * before that - there's no hook to correct it afterwards.
     */
    public function sanitizeLanguageRequest(): void
    {
        $allowed = config('adminer-bridge.languages');

        if (! is_array($allowed)) {
            return;
        }

        if (isset($_POST['lang']) && ! in_array($_POST['lang'], $allowed, true)) {
            unset($_POST['lang']);
        }

        if (isset($_COOKIE['adminer_lang']) && ! in_array($_COOKIE['adminer_lang'], $allowed, true)) {
            unset($_COOKIE['adminer_lang']);
        }

        if (isset($_SESSION['lang']) && ! in_array($_SESSION['lang'], $allowed, true)) {
            unset($_SESSION['lang']);
        }

        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = implode(',', $allowed);
    }
}
