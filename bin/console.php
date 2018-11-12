#!/usr/bin/env php
<?php

ini_set('error_log', __DIR__ . '/../logs/console.log');
ini_set('log_errors', true);

require_once __DIR__ . '/../vendor/autoload.php';

$settings = require __DIR__ . '/../src/settings.php';

$settings['settings']['logger']['path'] = 'php://stdout';
$settings['settings']['logger']['level'] = \Monolog\Logger::INFO;
$settings['settings']['logger']['formatter'] = 'console';

$container = new Slim\Container($settings);

require __DIR__ . '/../src/dependencies.php';

(new \Friendica\Directory\Controllers\Console($container, $argv))->execute();
