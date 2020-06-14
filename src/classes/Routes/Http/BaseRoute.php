<?php

namespace Friendica\Directory\Routes\Http;

/**
 * @author Hypolite Petovan <hypolite@mrpetovan.com>
 */
abstract class BaseRoute
{
	/**
	 * @var \Slim\Container
	 */
	protected $container;

	public function __construct(\Slim\Container $container)
	{
		$this->container = $container;
	}

	public abstract function __invoke(\Slim\Http\Request $request, \Slim\Http\Response $response, array $args): \Slim\Http\Response;
}
