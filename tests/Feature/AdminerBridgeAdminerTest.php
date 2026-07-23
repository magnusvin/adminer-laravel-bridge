<?php

declare(strict_types=1);

use Adminer\SqlDriver;
use AdminerBridge\AdminerBridge\AdminerBridgeAdminer;

beforeEach(function () {
    require_once __DIR__.'/../../vendor/vrana/adminer/adminer/include/adminer.inc.php';
    require_once __DIR__.'/../../vendor/vrana/adminer/adminer/include/driver.inc.php';
    require_once __DIR__.'/../../vendor/vrana/adminer/adminer/include/plugin.inc.php';
    require_once __DIR__.'/../../vendor/vrana/adminer/adminer/include/html.inc.php';
    require_once __DIR__.'/../../vendor/vrana/adminer/adminer/include/design.inc.php';
    require_once __DIR__.'/../../vendor/vrana/adminer/adminer/include/functions.inc.php';
});

function makeAdminerBridgeAdminer(
    bool $permanentLoginEnabled = true,
    bool $jushEnabled = true,
    bool $versionCheckEnabled = true,
    ?array $allowedDrivers = null,
    ?array $allowedLanguages = null,
    ?string $productionWarningText = null,
    string $productionWarningColor = '#b91c1c',
    string $productionWarningPosition = 'top',
    ?AdminerDesigns $designs = null,
): AdminerBridgeAdminer {
    return new AdminerBridgeAdminer(
        $permanentLoginEnabled,
        $jushEnabled,
        $versionCheckEnabled,
        $allowedDrivers,
        $allowedLanguages,
        $productionWarningText,
        $productionWarningColor,
        $productionWarningPosition,
        $designs,
    );
}

it('omits jush links entirely when jush is disabled', function () {
    $adminer = makeAdminerBridgeAdminer(jushEnabled: false);

    ob_start();
    $adminer->head(null);
    $html = ob_get_clean();

    expect($html)->not->toContain('jush');
});

it('neutralizes the version check script when disabled', function () {
    $adminer = makeAdminerBridgeAdminer(versionCheckEnabled: false);

    ob_start();
    $adminer->head(null);
    $html = ob_get_clean();

    expect($html)->toContain('verifyVersion = () => { };');
});

it('does not touch the version check when enabled', function () {
    $adminer = makeAdminerBridgeAdminer(versionCheckEnabled: true);

    ob_start();
    $adminer->head(null);
    $html = ob_get_clean();

    expect($html)->not->toContain('verifyVersion');
});

it('injects a deferred production warning banner script when configured', function () {
    $adminer = makeAdminerBridgeAdminer(productionWarningText: 'Careful, this is production!', productionWarningColor: '#123456', productionWarningPosition: 'bottom');

    ob_start();
    $adminer->head(null);
    $html = ob_get_clean();

    expect($html)->toContain("banner.textContent = 'Careful, this is production!';")
        ->toContain('background:#123456')
        ->toContain('bottom:0');
});

it('reserves space for a top/bottom banner instead of overlaying page content', function () {
    $adminer = makeAdminerBridgeAdminer(productionWarningText: 'Careful!', productionWarningPosition: 'top');

    ob_start();
    $adminer->head(null);
    $html = ob_get_clean();

    // #menu, #lang, #breadcrumb, and .logout are all `position: absolute` with
    // no positioned ancestor of their own, so body padding alone doesn't push
    // them out of the banner's way - body must become their containing block
    // by turning `position: relative` on first.
    expect($html)->toContain("document.body.style.position = 'relative';")
        ->toContain("document.body.style.paddingTop = banner.offsetHeight + 'px';");
});

it('renders the production warning as a non-overlaying corner badge when positioned left', function () {
    $adminer = makeAdminerBridgeAdminer(productionWarningText: 'Careful!', productionWarningPosition: 'left');

    ob_start();
    $adminer->head(null);
    $html = ob_get_clean();

    expect($html)->toContain('left:.5em;bottom:.5em')
        ->not->toContain('right:0')
        ->not->toContain('document.body.style.padding');
});

it('omits the production warning banner script when no text is configured', function () {
    $adminer = makeAdminerBridgeAdminer(productionWarningText: null);

    ob_start();
    $adminer->head(null);
    $html = ob_get_clean();

    expect($html)->not->toContain('banner');
});

it('produces no syntax highlighting output when jush is disabled', function () {
    $adminer = makeAdminerBridgeAdminer(jushEnabled: false);

    ob_start();
    $adminer->syntaxHighlighting([]);
    $html = ob_get_clean();

    expect($html)->toBe('');
});

it('merges design CSS into the base stylesheet list', function () {
    $_SESSION = ['design' => null];
    $designs = new AdminerDesigns(['https://example.com/theme.css' => 'Theme']);
    $adminer = makeAdminerBridgeAdminer(designs: $designs);

    expect($adminer->css())->toBe([]);
});

it('filters SqlDriver::$drivers down to the allow-list', function () {
    SqlDriver::$drivers = ['server' => 'MySQL / MariaDB', 'pgsql' => 'PostgreSQL', 'sqlite' => 'SQLite'];

    $adminer = makeAdminerBridgeAdminer(allowedDrivers: ['sqlite']);

    (new ReflectionMethod($adminer, 'filterAllowedDrivers'))->invoke($adminer);

    expect(SqlDriver::$drivers)->toBe(['sqlite' => 'SQLite']);
});

it('leaves SqlDriver::$drivers untouched when no allow-list is configured', function () {
    SqlDriver::$drivers = ['server' => 'MySQL / MariaDB', 'sqlite' => 'SQLite'];

    $adminer = makeAdminerBridgeAdminer(allowedDrivers: null);

    (new ReflectionMethod($adminer, 'filterAllowedDrivers'))->invoke($adminer);

    expect(SqlDriver::$drivers)->toBe(['server' => 'MySQL / MariaDB', 'sqlite' => 'SQLite']);
});

it('collapses the driver field to a hidden input once only one remains', function () {
    SqlDriver::$drivers = ['sqlite' => 'SQLite'];

    $adminer = makeAdminerBridgeAdminer(allowedDrivers: ['sqlite']);

    $field = $adminer->loginFormField('driver', '<tr><th>System<td>', '<select>...</select>');

    expect($field)->toBe("<input type='hidden' name='auth[driver]' value='sqlite'>\n");
});

it('leaves other login fields untouched', function () {
    $adminer = makeAdminerBridgeAdminer(allowedDrivers: ['sqlite']);

    $field = $adminer->loginFormField('username', '<tr><th>Username<td>', '<input name="auth[username]">');

    expect($field)->toBe('<tr><th>Username<td><input name="auth[username]">'."\n");
});

it('removes the language switcher entirely when one or fewer languages are allowed', function () {
    $adminer = makeAdminerBridgeAdminer(allowedLanguages: ['en']);
    $html = "<h1>Adminer</h1>\n<form action='' method='post'>\n<div id='lang'><label>Language: <select name='lang'><option value=\"en\" selected>English<option value=\"de\">Deutsch<option value=\"fr\">Français</select></label> <input type='submit' value='Use' class='hidden'>\n<input type='hidden' name='token' value='1:2'>\n</div>\n</form>\n<p>after</p>";

    $result = (new ReflectionMethod($adminer, 'restrictLanguageSwitcher'))->invoke($adminer, $html);

    expect($result)->toBe("<h1>Adminer</h1>\n<p>after</p>");
});

it('strips disallowed languages but keeps the switcher when more than one is allowed', function () {
    $adminer = makeAdminerBridgeAdminer(allowedLanguages: ['en', 'de']);
    $html = "<div id='lang'><label>Language: <select name='lang'><option value=\"en\" selected>English<option value=\"de\">Deutsch<option value=\"fr\">Français</select></label></div>\n</form>\n";
    $wrapped = "<form action='' method='post'>\n".$html;

    $result = (new ReflectionMethod($adminer, 'restrictLanguageSwitcher'))->invoke($adminer, $wrapped);

    expect($result)->toContain('value="en"')
        ->toContain('value="de"')
        ->not->toContain('value="fr"')
        ->not->toContain('Français');
});

it('leaves the page untouched when no language allow-list is configured', function () {
    $adminer = makeAdminerBridgeAdminer(allowedLanguages: null);
    $html = "<form action='' method='post'>\n<div id='lang'><select name='lang'><option value=\"en\">English<option value=\"fr\">Français</select></div>\n</form>\n";

    $result = (new ReflectionMethod($adminer, 'restrictLanguageSwitcher'))->invoke($adminer, $html);

    expect($result)->toBe($html);
});
