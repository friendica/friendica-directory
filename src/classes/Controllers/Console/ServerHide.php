<?php

namespace Friendica\Directory\Controllers\Console;

/**
 * @author Hypolite Petovan <hypolite@mrpetovan.com>
 */
class ServerHide extends \Asika\SimpleConsole\Console
{
	/**
	 * @var \Atlas\Pdo\Connection
	 */
	protected $atlas;

	/**
	 * @var \Friendica\Directory\Models\Server
	 */
	protected $serverModel;

	protected $helpOptions = ['h', 'help', '?'];

	public function __construct(
		\Atlas\Pdo\Connection $atlas,
		\Friendica\Directory\Models\Server $serverModel,
		?array $argv = null
	)
	{
		parent::__construct($argv);

		$this->atlas = $atlas;
		$this->serverModel = $serverModel;
	}

	protected function getHelp()
	{
		$help = <<<HELP
console server-hide - Toggle server hidden status
Usage
	bin/console server-hide <server_url> [-h|--help|-?] [-v]

Description
	Toggle server hidden status

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

		$server_url = trim($this->getArgument(0), '/');

		$server = $this->serverModel->getByUrlAlias($server_url);

		if (!$server) {
			throw new \RuntimeException('Unknown server with URL: ' . $server_url);
		}

		$result = $this->atlas->perform('UPDATE `server` SET `hidden` = 1 - `hidden` WHERE `id` = :id', ['id' => [$server['id'], \PDO::PARAM_INT]]);

		if (!$result) {
			throw new \RuntimeException('Unable to update server with ID: ' . $server['id']);
		}

		$this->out('Server successfully ' . ($server['hidden'] ? 'visible' : 'hidden'));

		return 0;
	}
}

