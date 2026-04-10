<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;

abstract class Controller
{
    /**
     * Ensure a path or URL uses the current request host (e.g., 127.0.0.1:8000 instead of localhost).
     */
    protected function withAppHost(string $urlOrPath): string
    {
        $root = request()->getSchemeAndHttpHost();
        $path = ltrim(parse_url($urlOrPath, PHP_URL_PATH) ?? $urlOrPath, '/');

        return rtrim($root, '/') . '/' . $path;
    }

    /**
     * Build an avatar URL from a stored profile path with existence check and fallback.
     */
    protected function avatarUrl(?string $rawPath): string
    {
        $filename = $rawPath ? basename($rawPath) : null;
        $sanitizedPath = $filename ? 'profiles/' . $filename : null;

        if ($sanitizedPath && Storage::disk('public')->exists($sanitizedPath)) {
            $storagePath = Storage::disk('public')->url($sanitizedPath);

            return $this->withAppHost($storagePath);
        }

        return $this->withAppHost(asset('images/avatar.jpg'));
    }

    /**
     * Build a URL from a public-disk path, normalized to current host.
     */
    protected function assetFromPublicDisk(string $path): string
    {
        return $this->withAppHost(Storage::disk('public')->url($path));
    }
}
