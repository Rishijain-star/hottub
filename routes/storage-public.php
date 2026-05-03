<?php

/**
 * Legacy /storage/… URLs → /uploads/app/public/…
 */
use Illuminate\Support\Facades\Route;

Route::get('storage/app/public/{path}', function (string $path) {
    return redirect('/uploads/app/public/' . ltrim(str_replace('\\', '/', $path), '/'), 301);
})->where('path', '.*');

Route::get('storage/{path}', function (string $path) {
    $path = ltrim(str_replace('\\', '/', $path), '/');
    if ($path === '' || str_contains($path, '..')) {
        abort(404);
    }

    return redirect('/uploads/app/public/' . $path, 301);
})->where('path', '.*');
