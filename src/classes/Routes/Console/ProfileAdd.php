<?php

namespace Friendica\Directory\Routes\Console;

/**
 * @author Hypolite Petovan <mrpetovan@gmail.com>
 */
class ProfileAdd extends BaseRoute
{
	public function __invoke(array $args)
	{
		return (new \Friendica\Directory\Controllers\Console\ProfileAdd(
			$this->container->get('\Friendica\Directory\Models\ProfilePollQueue'),
			$args
		));
	}
}
