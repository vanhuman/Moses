<?php

require_once HARM_START_UP_BASE_PATH . '/controllers/api.php';

$api = new \Harm\Api();
$uri = isset($_SERVER['REQUEST_URI']) ? explode('/', trim($_SERVER['REQUEST_URI'], '/')) : null;

if (is_null($uri) && isset($_SERVER['argv']) && is_array($_SERVER['argv'])) {
	$uri = $_SERVER['argv'];
}

array_shift($uri);

if (!$uri) {
	die();
}

$method = reset($uri);

if (method_exists($api, $method)) {
	array_shift($uri);
	$api->$method(... $uri);
} else {
	$api->index(... $uri);
}


