<?php

namespace Friendica\Directory\Controllers\Console;

/**
 * @author Hypolite Petovan <hypolite@mrpetovan.com>
 */
class ProfilePoll extends \Asika\SimpleConsole\Console
{
	/**
	 * @var \Friendica\Directory\Pollers\Profile
	 */
	protected $pollProfile;

	protected $helpOptions = ['h', 'help', '?'];

	public function __construct(\Friendica\Directory\Pollers\Profile $pollProfile, ?array $argv = null)
	{
		parent::__construct($argv);

		$this->pollProfile = $pollProfile;
	}

	protected function getHelp()
	{
		$help = <<<HELP
console profile-poll - Polls provided profile
Usage
	bin/console profile-poll <profile_url> [-h|--help|-?] [-v]

Description
	Polls provided profile

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

		$this->pollProfile->__invoke($this->getArgument(0));

		return 0;
	}
}

