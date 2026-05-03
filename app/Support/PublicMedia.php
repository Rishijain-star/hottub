<?php

namespace App\Support;

/**
 * Resolves stored paths and mixed URL strings to a browser-accessible URL.
 *
 * Stored paths are relative to public/uploads/app/public (filesystem "public" disk).
 * Legacy rows may contain old prefixes; {@see normalizeStoredPath()} canonicalises them.
 */
class PublicMedia
{
    /** Web path prefix for the public disk (after domain). */
    public const PUBLIC_MEDIA_PREFIX = '/uploads/app/public/';

    /**
     * Canonical path for persistence (relative to public/uploads/app/public).
     * Full http(s) URLs are returned unchanged.
     */
    public static function normalizeStoredPath(?string $path): ?string
    {
        if ($path === null) {
            return null;
        }
        $path = str_replace('\\', '/', trim($path));
        if ($path === '') {
            return null;
        }
        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }
        if (str_starts_with($path, '//')) {
            return 'https:' . $path;
        }

        $trimmed = ltrim($path, '/');

        foreach (
            [
                'storage/app/public/',
                'public/storage/',
                'public/uploads/app/public/',
                'uploads/app/public/',
                'public/uploads/',
            ] as $prefix
        ) {
            if (str_starts_with(strtolower($trimmed), strtolower($prefix))) {
                $trimmed = substr($trimmed, strlen($prefix));
                break;
            }
        }

        while (str_starts_with(strtolower($trimmed), 'app/public/')) {
            $trimmed = substr($trimmed, strlen('app/public/'));
        }

        if (str_starts_with(strtolower($trimmed), 'storage/app/public/')
            && ! str_starts_with(strtolower($trimmed), 'storage/app/')) {
            $trimmed = substr($trimmed, strlen('storage/app/public/'));
        }

        // Legacy short URL path saved as "uploads/featured/..." without app/public
        if (str_starts_with(strtolower($trimmed), 'uploads/')) {
            $trimmed = substr($trimmed, strlen('uploads/'));
            if (str_starts_with(strtolower($trimmed), 'app/public/')) {
                $trimmed = substr($trimmed, strlen('app/public/'));
            }
        }

        return $trimmed === '' ? null : $trimmed;
    }

    public static function url(?string $path): ?string
    {
        $n = self::normalizeStoredPath($path);
        if ($n === null) {
            return null;
        }
        if (preg_match('#^https?://#i', $n)) {
            $n = preg_replace('#/storage/app/public/#i', '/uploads/app/public/', $n);
            $n = preg_replace('#/storage/#i', '/uploads/app/public/', $n);
            // Short legacy: .../uploads/featured/... → .../uploads/app/public/featured/...
            $n = preg_replace('#(/uploads)/(?!app/public/)#i', '$1app/public/', $n);

            return $n;
        }

        $rel = ltrim(str_replace('\\', '/', $n), '/');

        while (str_starts_with(strtolower($rel), 'storage/app/public/')) {
            $rel = substr($rel, strlen('storage/app/public/'));
        }

        while (str_starts_with(strtolower($rel), 'app/public/')) {
            $rel = substr($rel, strlen('app/public/'));
        }

        if (str_starts_with(strtolower($rel), 'images/')) {
            return '/' . $rel;
        }

        while (str_starts_with(strtolower($rel), 'storage/')) {
            $rel = substr($rel, strlen('storage/'));
        }

        while (str_starts_with(strtolower($rel), 'uploads/')) {
            $rel = substr($rel, strlen('uploads/'));
        }
        if (str_starts_with(strtolower($rel), 'app/public/')) {
            $rel = substr($rel, strlen('app/public/'));
        }

        return '/uploads/app/public/' . ltrim($rel, '/');
    }
}
