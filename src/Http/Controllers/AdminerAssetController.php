<?php

declare(strict_types=1);

namespace AdminerBridge\AdminerBridge\Http\Controllers;

use Composer\InstalledVersions;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Mime\MimeTypes;

class AdminerAssetController
{
    /**
     * Adminer 6.0.1 moved jush from externals/ into adminer/static/jush, but
     * it lives there as a Git submodule - the Composer package ships that
     * directory empty. So the path Adminer links to is right while the files
     * behind it are not there, and jush requests are resolved against the
     * separate vrana/jush package instead.
     */
    public function static(string $file): BinaryFileResponse
    {
        if (str_starts_with($file, 'jush/')) {
            return $this->serveFrom(
                InstalledVersions::getInstallPath('vrana/jush'),
                substr($file, strlen('jush/')),
            );
        }

        return $this->serveFrom(InstalledVersions::getInstallPath('vrana/adminer').'/adminer/static', $file);
    }

    public function design(string $design, string $file): BinaryFileResponse
    {
        if (preg_match('/^[\w-]+$/', $design) !== 1) {
            throw new NotFoundHttpException;
        }

        return $this->serveFrom(InstalledVersions::getInstallPath('vrana/adminer').'/designs/'.$design, $file);
    }

    private function serveFrom(string $directory, string $file): BinaryFileResponse
    {
        $realDirectory = realpath($directory);
        $realPath = $realDirectory === false ? false : realpath($realDirectory.DIRECTORY_SEPARATOR.$file);

        if ($realDirectory === false || $realPath === false || ! str_starts_with($realPath, $realDirectory.DIRECTORY_SEPARATOR)) {
            throw new NotFoundHttpException;
        }

        $extension = pathinfo($realPath, PATHINFO_EXTENSION);
        $mimeType = MimeTypes::getDefault()->getMimeTypes($extension)[0] ?? null;

        return response()->file($realPath, $mimeType ? ['Content-Type' => $mimeType] : []);
    }
}
