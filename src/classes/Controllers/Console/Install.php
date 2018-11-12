<?php

namespace Friendica\Directory\Controllers\Console;

use Atlas\Pdo\Connection;
use Monolog\Logger;
use Seld\CliPrompt\CliPrompt;

/**
 * @author Hypolite Petovan <mrpetovan@gmail.com>
 */
class Install extends \Asika\SimpleConsole\Console
{
	/**
	 * @var Logger
	 */
	protected $logger;

	protected $helpOptions = ['h', 'help', '?'];

	public function __construct(
		Logger $logger,
		?array $argv = null
	)
	{
		parent::__construct($argv);

		$this->logger = $logger;
	}

	protected function getHelp()
	{
		$help = <<<HELP
console install - Install directory
Usage
	bin/console install <server_url> [-h|--help|-?] [-v]

Description
	Install directory

Options
    -h|--help|-? Show help information
    -v           Show more debug information.
HELP;
		return $help;
	}

	protected function doExecute()
	{
		if (count($this->args) == 0) {
			$this->out($this->getHelp());
			return 0;
		}

		if (count($this->args) > 1) {
			throw new \Asika\SimpleConsole\CommandArgsException('Too many arguments');
		}

		$this->out('Friendica Directory Install Wizard');
		$this->out('==================================');

		$config_file_path = __DIR__ . '/../../../../config/local.json';

		if (is_file($config_file_path)) {
			throw new \RuntimeException('Local config file already exists, did you want to run "bin/console dbupdate" ?');
		}

		if (!is_writable(dirname($config_file_path))) {
			throw new \RuntimeException('The config/ directory isn\'t writable, please check file permissions.');
		}

		$this->out('Warning: This will override any existing database!');


		do {
			$this->out('Please enter your database hostname [localhost] ', false);

			$host = CliPrompt::prompt();

			if (!$host) {
				$host = 'localhost';
			}

			do {
				$this->out('Please enter your database username: ', false);

				$user = CliPrompt::prompt();
			} while (!$user);

			$this->out('Please enter your database password: ', false);

			$pass = CliPrompt::hiddenPrompt();

			do {
				$this->out('Please enter your database name: ', false);

				$base = CliPrompt::prompt();
			} while (!$base);

			$localSettings = [
				'database' => [
					'driver' => 'mysql',
					'hostname' => $host,
					'database' => $base,
					'username' => $user,
					'password' => $pass,
				]
			];

			try {
				$dsn = "{$localSettings['database']['driver']}:dbname={$localSettings['database']['database']};host={$localSettings['database']['hostname']}";

				Connection::new($dsn, $localSettings['database']['username'], $localSettings['database']['password']);

				break;
			} catch (\Exception $ex) {
				$this->logger->error($ex->getMessage());
			} catch (\Throwable $e) {
				$this->logger->error($e->getMessage());
			}
		} while (true);

		$result = file_put_contents($config_file_path, json_encode($localSettings, JSON_PRETTY_PRINT));

		if (!$result) {
			throw new \RuntimeException('Unable to write to config/local.json, please check writing permissions.');
		}

		$this->out('Local config file successfully created.');

		$this->out('Initializing database schema...');

		$connectionUri = new \ByJG\Util\Uri("mysql://$user:$pass@$host/$base");

		$migration = new \ByJG\DbMigration\Migration($connectionUri, __DIR__ . '/../../../sql/');

		$migration->registerDatabase('mysql', \ByJG\DbMigration\Database\MySqlDatabase::class);

		$migration->reset();

		$this->out('Done.');

		$this->out(<<<'STDOUT'

Note: You still need to manually set up a cronjob like the following on *nix:
* * * * * cd /path/to/friendica-directory && bin/cron

======
To populate your directory, you can either:
- Add a new remote directory to pull from with "bin/console directory-add <directory URL>".
- Add it as the main directory in your Friendica admin settings.
STDOUT
		);

		return 0;
	}
}

