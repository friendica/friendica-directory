<?php

namespace Friendica\Directory\Routes\Console;

/**
 * @author Hypolite Petovan <hypolite@mrpetovan.com>
 */
class Install extends BaseRoute
{
	public function __invoke(array $args)
	{
		return (new \Friendica\Directory\Controllers\Console\Install(
			$this->container->get('logger')
		));
	}
}
