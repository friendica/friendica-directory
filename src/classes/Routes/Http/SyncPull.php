<?php

namespace Friendica\Directory\Routes\Http;

/**
 * @author Hypolite Petovan <hypolite@mrpetovan.com>
 */
class SyncPull extends BaseRoute
{
	public function __invoke(\Slim\Http\Request $request, \Slim\Http\Response $response, array $args): \Slim\Http\Response
	{
		return (new \Friendica\Directory\Controllers\Api\SyncPull(
			$this->container->atlas,
			$this->container->logger
		))->execute($request, $response, $args);
	}
}
