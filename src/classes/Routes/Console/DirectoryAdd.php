<?php

namespace Friendica\Directory\Routes\Console;

/**
 * @author Hypolite Petovan <mrpetovan@gmail.com>
 */
class DirectoryAdd extends BaseRoute
{
	public function __invoke(array $args)
	{
		return (new \Friendica\Directory\Controllers\Console\DirectoryAdd(
			$this->container->get('atlas'),
			$args
		));
	}
}
