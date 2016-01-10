<?php

Cms\Classes\CmsController::extend(function($controller) {
    $controller->middleware('Bm\Field\Classes\RoutingMiddleware');
});
