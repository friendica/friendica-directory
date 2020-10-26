<?php

namespace Friendica\Directory\Routes\Console;

/**
 * @author Hypolite Petovan <hypolite@mrpetovan.com>
 */
class ServerHide extends BaseRoute
{
	public function __invoke(array $args)
	{
		return (new \Friendica\Directory\Controllers\Console\ServerHide(
			$this->container->get('atlas'),
			$this->container->get(\Friendica\Directory\Models\Server::class),
			$args
		));
	}
}
