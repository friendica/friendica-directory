<?php

namespace Friendica\Directory\Routes\Web;

/**
 * @author Hypolite Petovan <hypolite@mrpetovan.com>
 */
class Statistics extends BaseRoute
{
	public function __construct(\Slim\Container $container)
	{
		parent::__construct($container);

		$this->controller = new \Friendica\Directory\Controllers\Web\Statistics(
			$this->container->atlas,
			$this->container->simplecache,
			$this->container->renderer
		);
	}
}
