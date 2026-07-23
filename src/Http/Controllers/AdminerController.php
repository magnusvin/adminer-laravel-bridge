<?php

declare(strict_types=1);

namespace AdminerBridge\AdminerBridge\Http\Controllers;

use AdminerBridge\AdminerBridge\AdminerBridge;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminerController
{
    /**
     * Adminer resolves its static assets and internal links relative to a
     * trailing-slash "directory" URL. Laravel's router treats `/adminer` and
     * `/adminer/` as the same route (it trims trailing slashes before
     * matching) and its URL generator does the same when building a redirect
     * target, so the trailing slash has to be restored by hand and the
     * response built directly instead of via the `redirect()` helper.
     */
    public function __invoke(Request $request, AdminerBridge $bridge): ?RedirectResponse
    {
        if ($request->route('any') === null && ! str_ends_with($request->getPathInfo(), '/')) {
            $query = $request->getQueryString();

            return new RedirectResponse($request->getPathInfo().'/'.($query ? "?{$query}" : ''));
        }

        $bridge->serve();

        return null;
    }
}
