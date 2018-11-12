<?php

namespace Friendica\Directory\Routes\Console;

/**
 * @author Hypolite Petovan <mrpetovan@gmail.com>
 */
class UpdateDb extends BaseRoute
{
	public function __invoke(array $args)
	{
		return (new \Friendica\Directory\Controllers\Console\UpdateDb(
			$this->container->get('logger'),
			$this->container->get('migration')
		));
	}
}
