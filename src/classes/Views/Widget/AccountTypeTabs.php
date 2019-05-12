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

	public function render(string $route_name, string $current_type = '', $condition = '', $values = [], array $queryParams = []): string
	{
		if ($condition) {
			$condition = 'AND ' . $condition;
		}

		$stmt = 'SELECT `account_type`, COUNT(*) AS `count`
			FROM `profile` p
			JOIN `server` s ON s.`id` = p.`server_id` AND s.`available` AND NOT s.`hidden`
			WHERE p.`available`
			AND NOT p.`hidden`
			' . $condition . '
			GROUP BY p.`account_type`
			ORDER BY `count` DESC';
		$account_types = $this->connection->fetchAll($stmt, $values);

		$tabs = [
			[
				'title' => $this->renderer->p__('account-type', 'All'),
				'link' => $this->router->pathFor($route_name, [], $queryParams),
				'active' => empty($current_type)
			]
		];

		foreach ($account_types as $account_type) {
			switch ($account_type['account_type']) {
				case 'People': $title = $this->renderer->np__('account-type', 'People (%d)', 'People (%d)', $account_type['count']); break;
				case 'Forum' : $title = $this->renderer->np__('account-type', 'Forum (%d)', 'Forums (%d)', $account_type['count']); break;
				default: $title = $this->renderer->np__('account-type', $account_type['account_type']. ' (%d)', $account_type['account_type']. ' (%d)', $account_type['count']);
			}

			$tabs[] = [
				'title' => $title,
				'link' => $this->router->pathFor($route_name, ['account_type' => strtolower($account_type['account_type'])], $queryParams),
				'active' => strtolower($account_type['account_type']) == strtolower($current_type),
				'disabled' => $account_type['count'] == 0
			];
		}

		$vars = [
			'tabs' => $tabs,
		];

		return $this->renderer->fetch('widget/accounttypetabs.phtml', $vars);
	}
}
