<?php

namespace Friendica\Directory\Routes\Console;

/**
 * @author Hypolite Petovan <mrpetovan@gmail.com>
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

	public abstract function __invoke(array $argv);
}
