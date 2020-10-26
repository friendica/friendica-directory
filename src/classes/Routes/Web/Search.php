<?php

namespace Friendica\Directory\Routes\Web;

/**
 * @author Hypolite Petovan <hypolite@mrpetovan.com>
 */
class Search extends BaseRoute
{
	public function __construct(\Slim\Container $container)
	{
		parent::__construct($container);

		$this->controller = new \Friendica\Directory\Controllers\Web\Search(
			$this->container->atlas,
			$this->container->get(\Friendica\Directory\Models\Profile::class),
			$this->container->get(\Friendica\Directory\Views\Widget\AccountTypeTabs::class),
			$this->container->renderer,
			$this->container->l10n
		);
	}
}
