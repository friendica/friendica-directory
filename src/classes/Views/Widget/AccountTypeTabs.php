<?php

namespace Friendica\Directory\Views\Widget;

/**
 * @author Hypolite Petovan <mrpetovan@gmail.com>
 */
class AccountTypeTabs
{
	/**
	 * @var \Atlas\Pdo\Connection
	 */
	private $connection;
	/**
	 * @var \Friendica\Directory\Views\PhpRenderer
	 */
	private $renderer;
	/**
	 * @var \Slim\Router
	 */
	private $router;

	public function __construct(\Atlas\Pdo\Connection $connection, \Friendica\Directory\Views\PhpRenderer $renderer, \Slim\Router $router)
	{
		$this->connection = $connection;
		$this->renderer = $renderer;
		$this->router = $router;
	}

	public function render(string $route_name, string $current_type = '', array $queryParams = []): string
	{
		$stmt = '
SELECT DISTINCT(`account_type`) AS `account_type`
FROM `profile`
WHERE `available`
AND NOT `hidden`';
		$account_types = $this->connection->fetchAll($stmt);

		$tabs = [
			[
				'title' => 'All',
				'link' => $this->router->pathFor($route_name, [], $queryParams),
				'active' => empty($current_type)
			]
		];

		foreach ($account_types as $account_type) {
			$tabs[] = [
				'title' => $account_type['account_type'],
				'link' => $this->router->pathFor($route_name, ['account_type' => strtolower($account_type['account_type'])], $queryParams),
				'active' => strtolower($account_type['account_type']) == strtolower($current_type)
			];
		}

		$vars = [
			'tabs' => $tabs,
		];

		return $this->renderer->fetch('widget/accounttypetabs.phtml', $vars);
	}
}
