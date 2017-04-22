<?php

use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;

Router::plugin('Xintesa/Assets', ['path' => '/'], function (RouteBuilder $route) {
    $route->prefix('admin', function (RouteBuilder $route) {

		$route->scope('/assets', [], function(RouteBuilder $route) {
			$route->extensions(['json']);
			$route->fallbacks();
		});
    });
});
