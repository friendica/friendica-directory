<?php

namespace Friendica\Directory\Routes\Console;

/**
 * @author Hypolite Petovan <hypolite@mrpetovan.com>
 */
class ProfileAdd extends BaseRoute
{
	public function __invoke(array $args)
	{
		return (new \Friendica\Directory\Controllers\Console\ProfileAdd(
			$this->container->get(\Friendica\Directory\Models\ProfilePollQueue::class),
			$args
		));
	}
}
