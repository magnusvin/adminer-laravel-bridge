<?php

declare(strict_types=1);

namespace AdminerBridge\AdminerBridge\Http\Controllers;

use Composer\InstalledVersions;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Mime\MimeTypes;

class AdminerAssetController
{
    public function static(string $file): BinaryFileResponse
    {
        return $this->serveFrom(InstalledVersions::getInstallPath('vrana/adminer').'/adminer/static', $file);
    }

    public function jush(string $file): BinaryFileResponse
    {
        return $this->serveFrom(InstalledVersions::getInstallPath('vrana/jush'), $file);
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
