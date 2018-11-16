<?php

namespace Friendica\Directory\Views\Widget;

/**
 * @author Hypolite Petovan <mrpetovan@gmail.com>
 */
class PopularLanguages
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
		$stmt = 'SELECT `language`, COUNT(*) AS `total` FROM `profile` WHERE `language` IS NOT NULL GROUP BY `language` ORDER BY COUNT(`language`) DESC LIMIT 10';
		$languages = $this->connection->fetchAll($stmt);

		$vars = [
			'languages' => $languages
		];

		return $this->renderer->fetch('widget/popularlanguages.phtml', $vars);
	}
}
