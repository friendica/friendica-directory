<?php

namespace Friendica\Directory\Controllers\Console;

use Monolog\Logger;

/**
 * @author Hypolite Petovan <mrpetovan@gmail.com>
 */
class UpdateDb extends \Asika\SimpleConsole\Console
{
	/**
	 * @var Logger
	 */
	protected $logger;
	/**
	 * @var \ByJG\DbMigration\Migration
	 */
	protected $migration;

	protected $helpOptions = ['h', 'help', '?'];

	public function __construct(
		Logger $logger,
		\ByJG\DbMigration\Migration $migration,
		?array $argv = null
	)
	{
		parent::__construct($argv);

		$this->logger = $logger;
		$this->migration = $migration;
	}

	protected function getHelp()
	{
		$help = <<<HELP
console updatedb - Update database schema
Usage
	bin/console updatedb <server_url> [-h|--help|-?] [-v]

Description
	Update database schema

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

		$this->out('Updating database schema to latest version...');

		$this->migration->up();

		$this->out('Database schema migrated to version ' . $this->migration->getCurrentVersion()['version']);

		return 0;
	}
}

