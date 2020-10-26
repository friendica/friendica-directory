#!/usr/bin/env php
<?php

ini_set('error_log', __DIR__ . '/../logs/cron.log');
ini_set('log_errors', true);
ini_set('display_errors', false);

require_once __DIR__ . '/../vendor/autoload.php';

$settings = require __DIR__ . '/../src/settings.php';

$container = new Slim\Container($settings);

require __DIR__ . '/../src/dependencies.php';

(new \Friendica\Directory\Controllers\Cron(
	$container->get('atlas'),
	$container->get(\Friendica\Directory\Pollers\Profile::class),
	$container->get(\Friendica\Directory\Pollers\Server::class),
	$container->get(\Friendica\Directory\Pollers\Directory::class),
	$container->get('logger')
))->execute();

