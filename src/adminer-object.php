<?php

declare(strict_types=1);

use AdminerBridge\AdminerBridge\AdminerBridgeAdminer;
use Composer\InstalledVersions;

if (! function_exists('adminer_object')) {
    function adminer_object(): AdminerBridgeAdminer
    {
        $allowedDrivers = config('adminer-bridge.drivers');
        $allowedLanguages = config('adminer-bridge.languages');

        $productionWarning = config('adminer-bridge.production_warning', []);
        $productionWarningEnabled = $productionWarning['enabled'] ?? null;

        if ($productionWarningEnabled === null) {
            $environments = (array) ($productionWarning['environments'] ?? ['production']);
            $productionWarningEnabled = $environments !== [] && app()->environment(...$environments);
        }

        $productionWarningText = $productionWarningEnabled
            ? (string) ($productionWarning['text'] ?? '')
            : null;

        return new AdminerBridgeAdminer(
            permanentLoginEnabled: (bool) config('adminer-bridge.permanent_login', true),
            jushEnabled: (bool) config('adminer-bridge.jush', true),
            versionCheckEnabled: (bool) config('adminer-bridge.version_check', true),
            allowedDrivers: is_array($allowedDrivers) ? array_values($allowedDrivers) : null,
            allowedLanguages: is_array($allowedLanguages) ? array_values($allowedLanguages) : null,
            productionWarningText: $productionWarningText,
            productionWarningColor: (string) ($productionWarning['color'] ?? '#b91c1c'),
            productionWarningPosition: in_array($productionWarning['position'] ?? 'top', ['bottom', 'left'], true)
                ? $productionWarning['position']
                : 'top',
            designs: adminer_bridge_designs(),
        );
    }
}

if (! function_exists('adminer_bridge_designs')) {
    function adminer_bridge_designs(): ?AdminerDesigns
    {
        $themes = config('adminer-bridge.themes');

        if (! is_array($themes) || $themes === []) {
            return null;
        }

        $designsDirectory = InstalledVersions::getInstallPath('vrana/adminer').'/designs';
        $urls = [];

        foreach ($themes as $theme) {
            foreach (['adminer.css' => '', 'adminer-dark.css' => ' (dark)'] as $file => $suffix) {
                if (is_file("{$designsDirectory}/{$theme}/{$file}")) {
                    $url = route('adminer-bridge.design', ['design' => $theme, 'file' => $file]);
                    $urls[$url] = ucfirst(str_replace(['-', '_'], ' ', $theme)).$suffix;
                }
            }
        }

        return $urls === [] ? null : new AdminerDesigns($urls);
    }
}
