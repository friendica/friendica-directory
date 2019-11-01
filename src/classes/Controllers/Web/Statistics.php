<?php

namespace Friendica\Directory\Controllers\Web;

/**
 * @author Hypolite Petovan <mrpetovan@gmail.com>
 */
class Statistics extends BaseController
{
	/**
	 * @var \Atlas\Pdo\Connection
	 */
	private $connection;
	/**
	 * @var \Psr\SimpleCache\CacheInterface
	 */
	private $simplecache;
	/**
	 * @var \Friendica\Directory\Views\PhpRenderer
	 */
	private $renderer;

	public function __construct(
		\Atlas\Pdo\Connection $atlas,
		\Psr\SimpleCache\CacheInterface $simplecache,
		\Friendica\Directory\Views\PhpRenderer $renderer
	)
	{
		$this->connection = $atlas;
		$this->simplecache = $simplecache;
		$this->renderer = $renderer;
	}

	public function render(\Slim\Http\Request $request, \Slim\Http\Response $response, array $args): array
	{
		$profilePollQueueCount = $this->connection->fetchValue('SELECT COUNT(*) FROM `profile_poll_queue`');

		$profileCounts = $this->connection->fetchOne(
			'SELECT
				COUNT(*) AS `total`,
				SUM(CASE WHEN `available` THEN 1 ELSE 0 END) AS `available`,
				SUM(CASE WHEN `language` IS NOT NULL THEN 1 ELSE 0 END) AS `language`
			FROM `profile`
			WHERE NOT `hidden`');

		$stmt = 'SELECT `language`, COUNT(*) AS `total`, COUNT(*) / :total  AS `ratio`
			FROM `profile`
			WHERE `language` IS NOT NULL
			AND `available` 
			AND NOT `hidden`
			GROUP BY `language`
			ORDER BY `total` DESC';

		$profileLanguages = $this->connection->fetchAll($stmt, ['total' => $profileCounts['language']]);

		$stable_version = $this->simplecache->get('stable_version');
		if (!$stable_version) {
			$stable_version = trim(file_get_contents('https://git.friendi.ca/friendica/friendica/raw/branch/master/VERSION'));
			$this->simplecache->set('stable_version', $stable_version);
		}

		$dev_version = $this->simplecache->get('dev_version');
		if (!$dev_version) {
			$dev_version = trim(file_get_contents('https://git.friendi.ca/friendica/friendica/raw/branch/develop/VERSION'));
			$this->simplecache->set('dev_version', $dev_version);
		}

		$rc_version = str_replace('-dev', '-rc', $dev_version);

		$serverPollQueueCount = $this->connection->fetchValue('SELECT COUNT(*) FROM `server_poll_queue`');

		$serverCounts = $this->connection->fetchOne(
			'SELECT
				COUNT(*) AS `total`,
				SUM(CASE WHEN `available` THEN 1 ELSE 0 END) AS `available`,
				SUM(CASE WHEN `available` AND `language` IS NOT NULL THEN 1 ELSE 0 END) AS `language`,
				SUM(CASE WHEN `available` AND `reg_policy` != "REGISTER_CLOSED" THEN 1 ELSE 0 END) AS `open`,
				SUM(CASE WHEN `available` AND `version` IS NOT NULL THEN 1 ELSE 0 END) AS `version`,
				SUM(CASE WHEN `available` AND (`version` = :dev_version OR `version` = :rc_version) THEN 1 ELSE 0 END) AS `dev_version`,
				SUM(CASE WHEN `available` AND `version` = :stable_version THEN 1 ELSE 0 END) AS `stable_version`,
				SUM(CASE WHEN `available` AND `version` != :dev_version AND `version` != :stable_version AND `version` != :rc_version THEN 1 ELSE 0 END) AS `outdated_version`
			FROM `server`
			WHERE NOT `hidden`', ['dev_version' => $dev_version, 'rc_version' => $rc_version, 'stable_version' => $stable_version]);

		$stmt = 'SELECT LEFT(`language`, 2) AS `language`, COUNT(*) AS `total`, COUNT(*) / :total AS `ratio`
			FROM `server`
			WHERE `language` IS NOT NULL
			AND `available` 
			AND NOT `hidden`
			GROUP BY LEFT(`language`, 2)
			ORDER BY `total` DESC';

		$serverLanguages = $this->connection->fetchAll($stmt, ['total' => $serverCounts['language']]);

		$stmt = 'SELECT `version`, COUNT(*) AS `total`, COUNT(*) / :total AS `ratio`
			FROM `server`
			WHERE `version` IS NOT NULL
			AND `available` 
			AND NOT `hidden`
			GROUP BY `version`
			ORDER BY `total` DESC';

		$serverVersions = $this->connection->fetchAll($stmt, ['total' => $serverCounts['version']]);

		$vars = [
			'stats' => [
				'profile_queue' => [
					'total' => $profilePollQueueCount
				],
				'profile' => [
					'total' => $profileCounts['total'],
					'ratio' => $profileCounts['total'] / $profilePollQueueCount,
					'available' => [
						'total' => $profileCounts['available'],
						'ratio' => $profileCounts['available'] / $profileCounts['total']
					],
					'language' => [
						'total' => $profileCounts['language'],
						'ratio' => $profileCounts['language'] / $profileCounts['available']
					],
					'languages' => $profileLanguages,
				],
				'server_queue' => [
					'total' => $serverPollQueueCount
				],
				'server' => [
					'total' => $serverCounts['total'],
					'ratio' => $serverCounts['total'] / $serverPollQueueCount,
					'available' => [
						'total' => $serverCounts['available'],
						'ratio' => $serverCounts['available'] / $serverCounts['total']
					],
					'language' => [
						'total' => $serverCounts['language'],
						'ratio' => $serverCounts['language'] / $serverCounts['available']
					],
					'open' => [
						'total' => $serverCounts['open'],
						'ratio' => $serverCounts['open'] / $serverCounts['available']
					],
					'version' => [
						'total' => $serverCounts['version'],
						'ratio' => $serverCounts['version'] / $serverCounts['available']
					],
					'languages' => $serverLanguages,
					'versions' => $serverVersions,
				],
			],
			'dev_version' => $dev_version,
			'rc_version' => $rc_version,
			'stable_version' => $stable_version,
		];

		$content = $this->renderer->fetch('statistics.phtml', $vars);

		// Render index view
		return ['content' => $content, 'noNavSearch' => true];
	}
}
