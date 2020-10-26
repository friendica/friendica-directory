<?php

namespace Friendica\Directory\Routes\Console;

/**
 * @author Hypolite Petovan <hypolite@mrpetovan.com>
 */
class ServerPoll extends BaseRoute
{
	public function __invoke(array $args)
	{
		return (new \Friendica\Directory\Controllers\Console\ServerPoll(
			$this->container->get('atlas'),
			$this->container->get(\Friendica\Directory\Pollers\Server::class),
			$args
		));
	}
}
