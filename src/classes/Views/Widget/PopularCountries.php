<?php

namespace Friendica\Directory\Views\Widget;

/**
 * @author Hypolite Petovan <mrpetovan@gmail.com>
 */
class PopularCountries
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
		$stmt = '
SELECT `country`, COUNT(`country`) AS `total`
FROM `profile`
WHERE `country` != ""
AND `available`
GROUP BY `country`
ORDER BY COUNT(`country`) DESC
LIMIT 10';
		$countries = $this->connection->fetchAll($stmt);

		$vars = [
			'countries' => $countries
		];

		return $this->renderer->fetch('widget/popularcountries.phtml', $vars);
	}
}
