<?php

namespace Friendica\Directory\Routes\Console;

/**
 * @author Hypolite Petovan <hypolite@mrpetovan.com>
 */
class ExtractStrings extends BaseRoute
{
	public function __invoke(array $args)
	{
		return (new \Friendica\Directory\Controllers\Console\ExtractStrings(
			$this->container->get('settings')['i18n']['path'],
			$args
		));
	}
}
