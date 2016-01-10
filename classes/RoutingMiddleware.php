<?php namespace Bm\Field\Classes;

use App;
use Closure;
use Config;
use System\Models\File;
use Bm\Field\Models\Post;
use Bm\Field\Models\Category;

/**
 * Routing
 *
 * @package bm\field
 * @author ziemowit.rosiak@blueservices.pl
 */
class RoutingMiddleware
{
    /**
     * Run the request filter.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $url = $request->getPathInfo();

        if (strpos('/backend', $url) === false) {
            if (substr($url, 0, 9) === "/download") {
                $path = Config::get('filesystems.disks.local.root', storage_path() . '/app');
                $file = $path . str_replace(["/download", "storage/app/"], "", urldecode($url));
                
                if (file_exists($file) && is_file($file)) {
                    return response()->download($file);
                }
            } elseif ($url !== "/") {
                $url = Post::checkUrl($url);
            }
        }

        return App::make('Cms\Classes\Controller')->run($url);
    }
}
