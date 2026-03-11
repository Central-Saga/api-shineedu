<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/ping', function () {
    return response('pong', 200);
});

// Serve storage files in local dev environment to avoid PHP built-in server 403 Forbidden
if (app()->environment('local', 'development')) {
    Route::get('/storage/{path}', function ($path) {
        $absPath = storage_path('app/public/' . $path);
        if (!file_exists($absPath)) {
            abort(404);
        }

        $mime = mime_content_type($absPath) ?: 'application/octet-stream';
        return response(file_get_contents($absPath), 200, [
        'Content-Type' => $mime,
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Methods' => 'GET',
        ]);
    })->where('path', '.*');
}
