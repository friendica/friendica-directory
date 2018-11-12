<?php

namespace Friendica\Directory\Controllers\Console;

/**
 * @author Hypolite Petovan <mrpetovan@gmail.com>
 */
class ServerPoll extends \Asika\SimpleConsole\Console
{
	/**
	 * @var \Atlas\Pdo\Connection
	 */
	protected $atlas;
	/**
	 * @var \Friendica\Directory\Pollers\Server
	 */
	protected $pollServer;

	protected $helpOptions = ['h', 'help', '?'];

	public function __construct(
		\Atlas\Pdo\Connection $atlas,
		\Friendica\Directory\Pollers\Server $pollServer,
		?array $argv = null
	)
	{
		parent::__construct($argv);

		$this->atlas = $atlas;
		$this->pollServer = $pollServer;
	}

	protected function getHelp()
	{
		$help = <<<HELP
console server-poll - Polls provided server
Usage
	bin/console server-poll <server_url> [-h|--help|-?] [-v]

Description
	Polls provided server

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

		$this->pollServer->__invoke($this->getArgument(0));

		return 0;
	}
}

