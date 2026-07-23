<?php

declare(strict_types=1);

namespace AdminerBridge\AdminerBridge;

use Adminer\Adminer;
use Adminer\SqlDriver;
use AdminerDesigns;

use function Adminer\h;
use function Adminer\js_escape;
use function Adminer\script;

class AdminerBridgeAdminer extends Adminer
{
    /**
     * @param  ?list<string>  $allowedDrivers  null allows every bundled driver
     * @param  ?list<string>  $allowedLanguages  null allows every bundled language
     * @param  'top'|'bottom'|'left'  $productionWarningPosition
     */
    public function __construct(
        private readonly bool $permanentLoginEnabled,
        private readonly bool $jushEnabled,
        private readonly bool $versionCheckEnabled,
        private readonly ?array $allowedDrivers,
        private readonly ?array $allowedLanguages,
        private readonly ?string $productionWarningText,
        private readonly string $productionWarningColor,
        private readonly string $productionWarningPosition,
        private readonly ?AdminerDesigns $designs,
    ) {}

    public function permanentLogin(bool $create = false): string
    {
        return $this->permanentLoginEnabled ? parent::permanentLogin($create) : '';
    }

    /**
     * Adminer hardcodes its jush asset links as "../externals/jush/…",
     * relative to wherever it's served from. Rather than fork the vendored
     * methods that print those links, let them run unchanged and rewrite the
     * known literal path in their output to point at our own route, fully
     * nested under the configured prefix - or drop the links entirely when
     * jush is disabled.
     *
     * The version-check script is neutralized the same way Adminer's own
     * bundled version-noverify.php plugin does it: redefine the client-side
     * verifyVersion() function to a no-op instead of trying to intercept the
     * hardcoded onload call in design.inc.php.
     */
    public function head(?bool $dark = null): bool
    {
        if ($this->jushEnabled) {
            ob_start();
            $linkFavicon = parent::head($dark);
            echo str_replace('../externals/jush/', $this->jushBaseUrl(), (string) ob_get_clean());
        } else {
            $linkFavicon = true;
        }

        if (! $this->versionCheckEnabled) {
            echo script('verifyVersion = () => { };');
        }

        if ($this->productionWarningText !== null && $this->productionWarningText !== '') {
            echo script($this->productionWarningScript());
        }

        return $linkFavicon;
    }

    /**
     * The menu this would naturally render into (see navigation()) is nested
     * inside Adminer's #foot element, which default.css hides by default
     * (`.js .foot { display: none; }`) until the hamburger menu is opened - so
     * a warning banner rendered there would be invisible on every JS-enabled
     * page load. document.body isn't available yet this early (head() runs
     * before <body> is even printed), so the banner is built and inserted
     * client-side once the DOM is ready, landing as a direct child of <body>
     * instead - a fixed-position sibling of #foot, always visible regardless
     * of its collapsed state.
     *
     * 'top'/'bottom' render a full-width bar, which would otherwise sit on
     * top of Adminer's own chrome - so body gets padded by the banner's own
     * rendered height to make room for it instead of overlapping it. That
     * alone isn't enough though: #menu, #lang, #breadcrumb, and .logout are
     * all `position: absolute` with no positioned ancestor of their own in
     * default.css, so - despite being nested inside #content in the markup -
     * their containing block is the viewport, not <body>, and body padding
     * has no effect on them. Giving <body> `position: relative` makes it
     * their containing block instead, so the padding correctly pushes them
     * down/up too instead of leaving the language switcher and database nav
     * stranded under the banner. 'left' renders a small corner badge instead
     * (mirroring how the design switcher already sits fixed in the opposite,
     * bottom-right corner without needing to reserve any space).
     */
    private function productionWarningScript(): string
    {
        $text = js_escape((string) $this->productionWarningText);
        $color = js_escape($this->productionWarningColor);

        if ($this->productionWarningPosition === 'left') {
            return <<<JS
                document.addEventListener('DOMContentLoaded', function () {
                \tvar banner = document.createElement('div');
                \tbanner.textContent = '{$text}';
                \tbanner.style.cssText = 'position:fixed;left:.5em;bottom:.5em;z-index:99999;background:{$color};color:#fff;padding:.4em .8em;font-weight:bold;border-radius:.25em;max-width:16em;';
                \tdocument.body.prepend(banner);
                });
                JS;
        }

        $edge = $this->productionWarningPosition === 'bottom' ? 'bottom' : 'top';
        $padding = $edge === 'top' ? 'paddingTop' : 'paddingBottom';

        return <<<JS
            document.addEventListener('DOMContentLoaded', function () {
            \tvar banner = document.createElement('div');
            \tbanner.textContent = '{$text}';
            \tbanner.style.cssText = 'position:fixed;left:0;right:0;{$edge}:0;z-index:99999;background:{$color};color:#fff;text-align:center;padding:.4em 1em;font-weight:bold;';
            \tdocument.body.prepend(banner);
            \tdocument.body.style.position = 'relative';
            \tdocument.body.style.{$padding} = banner.offsetHeight + 'px';
            });
            JS;
    }

    public function syntaxHighlighting(array $tables): void
    {
        if (! $this->jushEnabled) {
            return;
        }

        ob_start();
        parent::syntaxHighlighting($tables);
        echo str_replace('../externals/jush/', $this->jushBaseUrl(), (string) ob_get_clean());
    }

    public function css(): array
    {
        return array_merge(parent::css(), $this->designs?->css() ?? []);
    }

    public function afterConnect(): void
    {
        $this->designs?->afterConnect();
        parent::afterConnect();
    }

    /**
     * Prepend the design switcher (optional), then let the rest of the menu
     * render unchanged except for the language switcher, which gets rewritten
     * to the configured allow-list. The production warning banner is not
     * rendered here - see productionWarningScript() for why.
     */
    public function navigation(string $missing): void
    {
        $this->designs?->navigation($missing);

        ob_start();
        parent::navigation($missing);
        echo $this->restrictLanguageSwitcher((string) ob_get_clean());
    }

    public function loginForm(): void
    {
        $this->filterAllowedDrivers();

        parent::loginForm();
    }

    private function filterAllowedDrivers(): void
    {
        if ($this->allowedDrivers !== null) {
            SqlDriver::$drivers = array_intersect_key(SqlDriver::$drivers, array_flip($this->allowedDrivers));
        }
    }

    /**
     * Collapse the driver field to a hidden input once the allow-list has
     * narrowed it to a single option, instead of rendering a select with
     * nothing to actually choose between.
     */
    public function loginFormField(string $name, string $heading, string $value): string
    {
        if ($name === 'driver' && $this->allowedDrivers !== null && count(SqlDriver::$drivers) === 1) {
            $driverId = array_key_first(SqlDriver::$drivers);

            return "<input type='hidden' name='auth[driver]' value='".h($driverId)."'>\n";
        }

        return parent::loginFormField($name, $heading, $value);
    }

    private function jushBaseUrl(): string
    {
        $placeholder = '__jush_file__';

        return substr(route('adminer-bridge.jush', ['file' => $placeholder]), 0, -strlen($placeholder));
    }

    /**
     * switch_lang() is a plain procedural function (not an overridable
     * method), so the only way to restrict which languages it offers is to
     * rewrite its output: drop the whole switcher once one language remains,
     * or strip the disallowed <option> entries otherwise. The inner regex
     * only ever runs against the isolated switcher fragment matched by the
     * outer one, so it can't touch unrelated <option> elements on the page.
     */
    private function restrictLanguageSwitcher(string $html): string
    {
        if ($this->allowedLanguages === null) {
            return $html;
        }

        $allowed = $this->allowedLanguages;

        return (string) preg_replace_callback(
            "/<form action='' method='post'>\n<div id='lang'>.*?<\/div>\n<\/form>\n/s",
            static function (array $matches) use ($allowed): string {
                if (count($allowed) <= 1) {
                    return '';
                }

                return (string) preg_replace_callback(
                    '/<option value="([a-z-]+)"( selected)?>[^<]*(?=<option|<\/select>)/',
                    static fn (array $option): string => in_array($option[1], $allowed, true) ? $option[0] : '',
                    $matches[0],
                );
            },
            $html,
        );
    }
}
