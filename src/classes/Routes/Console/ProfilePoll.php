<?php

namespace Friendica\Directory\Routes\Console;

/**
 * @author Hypolite Petovan <mrpetovan@gmail.com>
 */
class ProfilePoll extends BaseRoute
{
	public function __invoke(array $args)
	{
		return (new \Friendica\Directory\Controllers\Console\ProfilePoll(
			$this->container->get('\Friendica\Directory\Pollers\Profile'),
			$args
		));
	}
}
