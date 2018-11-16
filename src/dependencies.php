<?php

use Interop\Container\ContainerInterface;

// DIC configuration

// l10n
$container['l10n'] = function (ContainerInterface $c): Gettext\TranslatorInterface {
	$translator = new Gettext\Translator();
	return $translator;
};

// simple cache
$container['simplecache'] = function (ContainerInterface $c): Sarahman\SimpleCache\FileSystemCache {
	$settings = $c->get('settings')['simplecache'];
	return new Sarahman\SimpleCache\FileSystemCache($settings['directory']);
};

// zend escaper
$container['escaper'] = function (ContainerInterface $c): Zend\Escaper\Escaper {
	$settings = $c->get('settings')['escaper'];
	return new Zend\Escaper\Escaper($settings['encoding']);
};

// view renderer
$container['renderer'] = function (ContainerInterface $c): Friendica\Directory\Views\PhpRenderer {
	$settings = $c->get('settings')['renderer'];
	return new Friendica\Directory\Views\PhpRenderer($c->get('escaper'), $c->get('l10n'), $settings['template_path']);
};

// monolog
$container['logger'] = function (ContainerInterface $c): Monolog\Logger {
	$settings = $c->get('settings')['logger'];
	$logger = new Monolog\Logger($settings['name']);
	$logger->pushProcessor(new Monolog\Processor\UidProcessor());

	switch ($settings['formatter']) {
		case 'console':
			$formatter = new Monolog\Formatter\LineFormatter("[%level_name%] %message% %context%\n");
			break;
		case 'logfile':
		default:
			$formatter = new Monolog\Formatter\LineFormatter("%datetime% - %level_name% %extra%: %message% %context%\n");
			break;
	}

	$handler = new Monolog\Handler\StreamHandler($settings['path'], $settings['level']);
	$handler->setFormatter($formatter);
	$logger->pushHandler($handler);
	return $logger;
};

// PDO wrapper
$container['atlas'] = function (ContainerInterface $c): Atlas\Pdo\Connection {
	$args = $c->get('settings')['database'];

	$dsn = "{$args['driver']}:dbname={$args['database']};host={$args['hostname']}";

	$atlasConnection = Atlas\Pdo\Connection::new($dsn, $args['username'], $args['password']);

	return $atlasConnection;
};

// Database migration manager
$container['migration'] = function (ContainerInterface $c): ByJG\DbMigration\Migration {
	$args = $c->get('settings')['database'];

	$connectionUri = new ByJG\Util\Uri("{$args['driver']}://{$args['username']}:{$args['password']}@{$args['hostname']}/{$args['database']}");

	$migration = new ByJG\DbMigration\Migration($connectionUri, __DIR__ . '/sql');

	$migration->addCallbackProgress(function (string $action, int $currentVersion) use ($c): void {
		switch($action) {
			case 'reset':   $c->get('logger')->notice('Resetting database schema'); break;
			case 'migrate': $c->get('logger')->notice('Migrating database schema to version ' . $currentVersion); break;
			default:
				$c->get('logger')->notice('Migration action: ' . $action . ' Current Version: ' . $currentVersion);
		}

	});

	$migration->registerDatabase('mysql', ByJG\DbMigration\Database\MySqlDatabase::class);

	return $migration;
};

// Internal Dependency Injection

$container['\Friendica\Directory\Models\Profile'] = function (ContainerInterface $c): Friendica\Directory\Models\Profile {
	return new Friendica\Directory\Models\Profile($c->get('atlas'));
};

$container['\Friendica\Directory\Models\ProfilePollQueue'] = function (ContainerInterface $c): Friendica\Directory\Models\ProfilePollQueue {
	return new Friendica\Directory\Models\ProfilePollQueue($c->get('atlas'));
};

$container['\Friendica\Directory\Models\Server'] = function (ContainerInterface $c): Friendica\Directory\Models\Server {
	return new Friendica\Directory\Models\Server($c->get('atlas'));
};

$container['\Friendica\Directory\Pollers\Directory'] = function (ContainerInterface $c): Friendica\Directory\Pollers\Directory {
	$settings = $c->get('settings')['poller'];
	return new Friendica\Directory\Pollers\Directory(
		$c->get('atlas'),
		$c->get('\Friendica\Directory\Models\ProfilePollQueue'),
		$c->get('logger'),
		$settings ?: []
	);
};

$container['\Friendica\Directory\Pollers\Profile'] = function (ContainerInterface $c): Friendica\Directory\Pollers\Profile {
	$settings = $c->get('settings')['poller'];
	return new Friendica\Directory\Pollers\Profile(
		$c->get('atlas'),
		$c->get('\Friendica\Directory\Models\Server'),
		$c->get('\Friendica\Directory\Models\Profile'),
		$c->get('logger'),
		$settings ?: []
	);
};

$container['\Friendica\Directory\Pollers\Server'] = function (ContainerInterface $c): Friendica\Directory\Pollers\Server {
	$settings = $c->get('settings')['poller'];
	return new Friendica\Directory\Pollers\Server(
		$c->get('atlas'),
		$c->get('\Friendica\Directory\Models\ProfilePollQueue'),
		$c->get('\Friendica\Directory\Models\Server'),
		$c->get('simplecache'),
		$c->get('logger'),
		$settings ?: []
	);
};

$container['\Friendica\Directory\Views\Widget\AccountTypeTabs'] = function (ContainerInterface $c): Friendica\Directory\Views\Widget\AccountTypeTabs {
	return new Friendica\Directory\Views\Widget\AccountTypeTabs(
		$c->get('atlas'),
		$c->get('renderer'),
		$c->get('router')
	);
};
