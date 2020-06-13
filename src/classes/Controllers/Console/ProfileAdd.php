<?php

namespace Friendica\Directory\Controllers\Console;

use Friendica\Directory\Models\ProfilePollQueue;

/**
 * @author Hypolite Petovan <hypolite@mrpetovan.com>
 */
class ProfileAdd extends \Asika\SimpleConsole\Console
{
	/**
	 * @var ProfilePollQueue
	 */
	protected $profilePollQueueModel;

	protected $helpOptions = ['h', 'help', '?'];

	public function __construct(
		ProfilePollQueue $profilePollQueueModel,
		?array $argv = null
	)
	{
		parent::__construct($argv);

		$this->profilePollQueueModel = $profilePollQueueModel;
	}

	protected function getHelp()
	{
		$help = <<<HELP
console profile-add - Adds provided profile to queue
Usage
	bin/console profile-add <profile_url> [-h|--help|-?] [-v]

Description
	Adds provided profile to queue

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

		$profile_url = $this->getArgument(0);

		$result = $this->profilePollQueueModel->add($profile_url);

		switch($result) {
			case 0: {
				$this->out('Successfully added the profile to the queue.');
				return 0;
				break;
			}
			case ProfilePollQueue::EMPTY_URL: {
				throw new \RuntimeException('Unable to add profile with empty URL');
			}
			case ProfilePollQueue::MISSING_HOST: {
				throw new \RuntimeException('Unable to add profile URL with a missing host');
			}
			case ProfilePollQueue::PRIVATE_HOST: {
				throw new \RuntimeException('Unable to add profile with a private URL');
			}
			case ProfilePollQueue::ALREADY_EXISTS: {
				$this->out('Profile already existing in the queue.');
				return 0;
			}
			default: {
				throw new \RuntimeException('Unable to add profile to the queue');
			}
		}
	}
}

