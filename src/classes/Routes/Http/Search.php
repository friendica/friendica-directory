<?php

namespace Friendica\Directory\Routes\Http;

/**
 * @author Hypolite Petovan <hypolite@mrpetovan.com>
 */
class Search extends BaseRoute
{
	public function __invoke(\Slim\Http\Request $request, \Slim\Http\Response $response, array $args): \Slim\Http\Response
	{
		return (new \Friendica\Directory\Controllers\Api\Search(
			$this->container->atlas,
			$this->container->get(\Friendica\Directory\Models\Profile::class),
			$this->container->l10n
		))->render($request, $response, $args);
	}
}
