<?php

namespace Friendica\Directory\Routes\Http;

/**
 * @author Hypolite Petovan <mrpetovan@gmail.com>
 */
class Servers extends BaseRoute
{
	public function __invoke(\Slim\Http\Request $request, \Slim\Http\Response $response, array $args): \Slim\Http\Response
	{
		return (new \Friendica\Directory\Controllers\Web\Servers(
			$this->container->atlas,
			$this->container->renderer,
			$this->container->l10n,
			$this->container->simplecache)
		)->render($request, $response);
	}
}
