<?php

namespace Friendica\Directory\Routes\Web;

/**
 * @author Hypolite Petovan <hypolite@mrpetovan.com>
 */
class Pages extends BaseRoute
{
	public function __construct(\Slim\Container $container, $pageFile)
	{
		parent::__construct($container);

		$this->controller = new \Friendica\Directory\Controllers\Web\Page(
			$pageFile
		);
	}
}
