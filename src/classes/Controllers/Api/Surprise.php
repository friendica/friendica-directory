<?php

namespace Friendica\Directory\Controllers\Api;

use Friendica\Directory\Content\Pager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @author Hypolite Petovan <mrpetovan@gmail.com>
 */
class Surprise
{
	/**
	 * @var \Atlas\Pdo\Connection
	 */
	private $atlas;

	public function __construct(
		\Atlas\Pdo\Connection $atlas
	)
	{
		$this->atlas = $atlas;
	}

	public function render(\Slim\Http\Request $request, \Slim\Http\Response $response, array $args): \Slim\Http\Response
	{
		$redirectUrl = '';

		$sql = 'SELECT `base_url`, server.*
			FROM `server`
			WHERE `reg_policy` = "REGISTER_OPEN"
			AND `health_score` > 75
			AND `ssl_state`
			AND `available`
			ORDER BY `health_score` DESC, RAND()';

		$server = $this->atlas->fetchOne($sql);
		if ($server) {
			$redirectUrl = $server['base_url'];
		}

		return $response->withRedirect($redirectUrl);
	}
}
