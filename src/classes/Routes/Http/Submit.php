<?php

namespace Friendica\Directory\Routes\Http;

/**
 * @author Hypolite Petovan <mrpetovan@gmail.com>
 */
class Submit extends BaseRoute
{
	public function __invoke(\Slim\Http\Request $request, \Slim\Http\Response $response, array $args): \Slim\Http\Response
	{
		return (new \Friendica\Directory\Controllers\Api\Submit(
			$this->container->atlas,
			$this->container->get('\Friendica\Directory\Models\ProfilePollQueue'),
			$this->container->logger
		))->execute($request, $response);
	}
}
