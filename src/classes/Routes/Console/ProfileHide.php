<?php

namespace Friendica\Directory\Routes\Console;

/**
 * @author Hypolite Petovan <hypolite@mrpetovan.com>
 */
class ProfileHide extends BaseRoute
{
	public function __invoke(array $args)
	{
		return (new \Friendica\Directory\Controllers\Console\ProfileHide(
			$this->container->get('atlas'),
			$args
		));
	}
}
