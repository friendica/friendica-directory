<?php

namespace Friendica\Directory\Controllers\Console;

use Friendica\Directory\Models\Profile;

/**
 * @author Hypolite Petovan <mrpetovan@gmail.com>
 */
class ProfileHide extends \Asika\SimpleConsole\Console
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
console profile-hide - Toggle profile hidden status
Usage
	bin/console profile-hide <profile_url> [-h|--help|-?] [-v]

Description
	Toggle profile hidden status

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

		$profile_url = trim($this->getArgument(0), '/');

		$profileInfo = Profile::extractInfoFromProfileUrl($profile_url);
		if (!$profileInfo) {
			throw new \RuntimeException('Invalid profile with URL: ' . $profile_url);
		}

		$profile = $this->atlas->fetchOne('SELECT * FROM `profile` WHERE `addr` = :addr', ['addr' => $profileInfo['addr']]);
		if (!$profile) {
			throw new \RuntimeException('Unknown profile with URL: ' . $profile_url);
		}

		$result = $this->atlas->fetchAffected('UPDATE `profile` SET `hidden` = 1 - `hidden` WHERE `id` = :id', ['id' => [$profile['id'], \PDO::PARAM_INT]]);
		if (!$result) {
			throw new \RuntimeException('Unable to update profile with ID: ' . $profile['id']);
		}

		$this->out('Profile successfully ' . ($profile['hidden'] ? 'visible' : 'hidden'));

		return 0;
	}
}

