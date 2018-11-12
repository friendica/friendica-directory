<?php

namespace Friendica\Directory\Routes\Console;

/**
 * @author Hypolite Petovan <mrpetovan@gmail.com>
 */
class ServerPoll extends BaseRoute
{
	public function __invoke(array $args)
	{
		return (new \Friendica\Directory\Controllers\Console\ServerPoll(
			$this->container->get('atlas'),
			$this->container->get('\Friendica\Directory\Pollers\Server'),
			$args
		));
	}
}
