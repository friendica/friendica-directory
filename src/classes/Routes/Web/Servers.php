<?php

namespace Friendica\Directory\Routes\Web;

/**
 * @author Hypolite Petovan <mrpetovan@gmail.com>
 */
class Servers extends BaseRoute
{
	public function __construct(\Slim\Container $container)
	{
		parent::__construct($container);

		$this->controller = new \Friendica\Directory\Controllers\Web\Servers(
			$this->container->atlas,
			$this->container->renderer,
			$this->container->l10n,
			$this->container->simplecache
		);
	}
}
