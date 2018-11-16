<?php

namespace Friendica\Directory\Controllers\Api;

use Slim\Http\Request;
use Slim\Http\Response;

/**
 * @author Hypolite Petovan <mrpetovan@gmail.com>
 */
class Photo
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

	public function render(Request $request, Response $response, array $args): Response
	{
		$data = $this->atlas->fetchValue(
			'SELECT `data` FROM `photo` WHERE `profile_id` = :profile_id',
			['profile_id' => $args['profile_id']]
		);

		if (!$data) {
			$data = file_get_contents('public/images/default-profile-sm.jpg');
		}

		//Try and cache our result.
		$etag = md5($data);

		$response = $response
			->withHeader('Etag', $etag)
			->withHeader('Expires', date('D, d M Y H:i:s' . ' GMT', strtotime('now + 1 week')))
			->withHeader('Cache-Control', 'max-age=' . intval(7 * 24 * 3600))
			->withoutHeader('Pragma');

		if ($request->getServerParam('HTTP_IF_NONE_MATCH') == $etag) {
			$response = $response->withStatus(304, 'Not Modified');
		} else {
			$response = $response->withHeader('Content-type', 'image/jpeg');
			$response->getBody()->write($data);
		}

		return $response;
	}
}
