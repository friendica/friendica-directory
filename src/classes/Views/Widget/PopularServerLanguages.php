<?php

namespace Friendica\Directory\Views\Widget;

/**
 * @author Hypolite Petovan <hypolite@mrpetovan.com>
 */
class PopularServerLanguages
{
	/**
	 * @var \Atlas\Pdo\Connection
	 */
	private $connection;
	/**
	 * @var \Friendica\Directory\Views\PhpRenderer
	 */
	private $renderer;

	public function __construct(\Atlas\Pdo\Connection $connection, \Friendica\Directory\Views\PhpRenderer $renderer)
	{
		$this->connection = $connection;
		$this->renderer = $renderer;
	}

	public function render(): string
	{
		$stmt = 'SELECT LEFT(`language`, 2) AS `language`, COUNT(*) AS `total`
			FROM `server`
			WHERE `reg_policy` != "REGISTER_CLOSED"
			AND `available`
			AND NOT `hidden`
			AND `language` IS NOT NULL
			GROUP BY LEFT(`language`, 2)
			ORDER BY `total` DESC
			LIMIT 10';
		$languages = $this->connection->fetchAll($stmt);

		$vars = [
			'languages' => $languages
		];

		return $this->renderer->fetch('widget/popularserverlanguages.phtml', $vars);
	}
}
