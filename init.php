<?php

use Bm\Field\Models\Post;

// pobieranie plików
Route::group(['prefix' => 'download'], function() {
    Route::get('{name}', function($name) {
        $path = Config::get('filesystems.disks.local.root', storage_path() . '/app');
        $file = $path . str_replace("storage/app/", "", urldecode($name));

        if (is_file($file)) {
            return response()->download($file);
        }
    })->where('name', '.*');
});

// artykuły i kategorie
Event::listen('cms.route', function() {
    Route::any('{slug}', function($slug) {
        return (new Cms\Classes\CmsController())->run(Post::checkUrl("/$slug"));
    })->where('slug', '(.*)?');
});
