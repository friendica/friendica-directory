<?php

namespace Friendica\Directory\Views\Widget;

/**
 * @author Hypolite Petovan <mrpetovan@gmail.com>
 */
class PopularTags
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
		$stmt = 'SELECT `term`, COUNT(*) AS `total` FROM `tag` GROUP BY `term` ORDER BY COUNT(`term`) DESC LIMIT 20';
		$tags = $this->connection->fetchAll($stmt);

		$vars = [
			'title' => 'Popular Tags',
			'tags' => $tags
		];

		return $this->renderer->fetch('widget/populartags.phtml', $vars);
	}
}
