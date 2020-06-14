<?php

namespace Friendica\Directory\Controllers\Console;

/**
 * @author Hypolite Petovan <hypolite@mrpetovan.com>
 */
class DirectoryPoll extends \Asika\SimpleConsole\Console
{
	/**
	 * @var \Atlas\Pdo\Connection
	 */
	protected $atlas;
	/**
	 * @var \Friendica\Directory\Pollers\Directory
	 */
	protected $pollDirectory;

	protected $helpOptions = ['h', 'help', '?'];

	public function __construct(
		\Atlas\Pdo\Connection $atlas,
		\Friendica\Directory\Pollers\Directory $pollDirectory,
		?array $argv = null
	)
	{
		parent::__construct($argv);

		$this->atlas = $atlas;
		$this->pollDirectory = $pollDirectory;
	}

	protected function getHelp()
	{
		$help = <<<HELP
console directory-poll - Polls provided directory
Usage
	bin/console directory-poll <directory_url> [-h|--help|-?] [-v]

Description
	Polls provided directory

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

		$this->pollDirectory->__invoke($this->getArgument(0));

		return 0;
	}
}

