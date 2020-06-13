<?php

namespace Friendica\Directory\Controllers\Api;

use Slim\Http\Request;
use Slim\Http\Response;

/**
 * @author Hypolite Petovan <hypolite@mrpetovan.com>
 */
class SyncPull
{
	/**
	 * @var \Atlas\Pdo\Connection
	 */
	private $atlas;
	/**
	 * @var \Psr\Log\LoggerInterface
	 */
	private $logger;

	public function __construct(
		\Atlas\Pdo\Connection $atlas,
		\Psr\Log\LoggerInterface $logger
	)
	{
		$this->atlas = $atlas;
		$this->logger = $logger;
	}

	public function execute(Request $request, Response $response, array $args): Response
	{
		$since = $args['since'] ?? null;

		$stmt = 'SELECT `profile_url`
			FROM `profile` p
			JOIN `server` s ON s.`id` = p.`server_id`
			WHERE p.`available`
			AND NOT p.`hidden`
			AND s.`available`
			AND NOT s.`hidden`';
		$values = [];

		if ($since) {
			$stmt .= '
			AND p.`updated` >= FROM_UNIXTIME(:since)';
			$values['since'] = [$since, \PDO::PARAM_INT];
		}

		$profiles = $this->atlas->fetchColumn($stmt, $values);

		$response = $response->withJson([
			'now' => time(),
			'count' => count($profiles),
			'results' => $profiles
		]);

		return $response;
	}
}
