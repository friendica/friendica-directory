<?php

namespace Friendica\Directory\Routes\Http;

/**
 * @author Hypolite Petovan <mrpetovan@gmail.com>
 */
class Directory extends BaseRoute
{
	public function __invoke(\Slim\Http\Request $request, \Slim\Http\Response $response, array $args): \Slim\Http\Response
	{
		return (new \Friendica\Directory\Controllers\Web\Directory(
			$this->container->atlas,
			$this->container->get('\Friendica\Directory\Models\Profile'),
			$this->container->get('\Friendica\Directory\Views\Widget\AccountTypeTabs'),
			$this->container->renderer,
			$this->container->l10n)
		)->render($request, $response, $args);
	}
}
