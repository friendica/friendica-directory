<?php

namespace Friendica\Directory\Controllers\Console;

/**
 * @author Hypolite Petovan <hypolite@mrpetovan.com>
 */
class DirectoryAdd extends \Asika\SimpleConsole\Console
{
	/**
	 * @var \Atlas\Pdo\Connection
	 */
	protected $atlas;

	protected $helpOptions = ['h', 'help', '?'];

	public function __construct(
		\Atlas\Pdo\Connection $atlas,
		?array $argv = null
	)
	{
		parent::__construct($argv);

		$this->atlas = $atlas;
	}

	protected function getHelp()
	{
		$help = <<<HELP
console directory-add - Adds provided directory to queue
Usage
	bin/console directory-add <directory_url> [-h|--help|-?] [-v]

Description
	Adds provided directory to queue

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

		$directory_url = $this->getArgument(0);

		$affected = $this->atlas->fetchAffected('INSERT IGNORE INTO `directory_poll_queue` SET
			`directory_url` = :directory_url',
			['directory_url' => $directory_url]
		);

		if (!$affected) {
			$this->out('Directory already exists in the queue.');
		} else {
			$this->out('Successfully added the directory to the queue.');
		}

		return 0;
	}
}

