<?php

/* Local settings; not checked into git. */
$localSettings = [];
if (\is_readable(__DIR__ . '/../config/local.json')) {
	$settingsFile = \file_get_contents(__DIR__ . '/../config/local.json');
	if (\is_string($settingsFile)) {
		/** @var array $localSettings */
		$localSettings = \json_decode($settingsFile, true);
	}
}

/*
 * Full directory settings list
 *
 * Please don't change any value here, instead put them into config/local.json following the same structure.
 */
$settings = [
	'displayErrorDetails' => false, // set to false in production
	'addContentLengthHeader' => false, // Allow the web server to send the content-length header
	'i18n' => [
		'locales' => ['en', 'fr'],
		'default' => 'en',
		'path' => __DIR__ . '/lang'
	],
	// Escaper settings
	'escaper' => [
		'encoding' => 'utf-8'
	],
	// Renderer settings
	'renderer' => [
		'template_path' => __DIR__ . '/templates',
	],
	// Monolog settings
	'logger' => [
		'name' => 'friendica-directory',
		'path' => __DIR__ . '/../logs/app.log',
		// This has to be translated to a numeric level (lowest priority 100-600 highest priority) in config/local.json
		'level' => \Monolog\Logger::WARNING,
		'formatter' => 'logfile',
	],
	// Dummy database settings, please run "bin/console install" to set them up for your instance
	'database' => [
		'driver' => 'mysql',
		'hostname' => 'localhost',
		'database' => 'friendica-directory',
		'username' => 'friendica-directory',
		'password' => 'friendica-directory',
	],
	'simplecache' => [
		'directory' => __DIR__ . '/../cache',
	],
	'poller' => [
		// Successful poll delay
		'directory_poll_delay' => 3600, // 1 hour
		'server_poll_delay' => 24 * 3600, // 1 day
		'profile_poll_delay' => 24 * 3600, // 1 day

		// Unsuccessful poll base delay
		'directory_poll_retry_base_delay' => 600, // 10 minutes
		'server_poll_retry_base_delay' => 1800, // 30 minutes
		'profile_poll_retry_base_delay' => 1800, // 30 minutes
	],
];

function settings_merge_recursive($defaults, $local) {
	$return = [];
	foreach ($defaults as $key => $value) {
		if (isset($local[$key])) {
			if (is_array($value)) {
				$return[$key] = settings_merge_recursive($value, $local[$key]);
			} else {
				$return[$key] = $local[$key];
			}
		} else {
			$return[$key] = $value;
		}
	}

	return $return;
}

return [
	'settings' => settings_merge_recursive($settings, $localSettings)
];

