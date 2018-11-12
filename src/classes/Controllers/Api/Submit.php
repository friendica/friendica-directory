<?php

namespace Friendica\Directory\Controllers\Api;

use Slim\Http\Request;
use Slim\Http\Response;

/**
 * @author Hypolite Petovan <mrpetovan@gmail.com>
 */
class Submit
{
	/**
	 * @var \Atlas\Pdo\Connection
	 */
	private $atlas;
	/**
	 * @var \Friendica\Directory\Models\ProfilePollQueue
	 */
	private $profilePollQueueModel;
	/**
	 * @var \Psr\Log\LoggerInterface
	 */
	private $logger;

	public function __construct(
		\Atlas\Pdo\Connection $atlas,
		\Friendica\Directory\Models\ProfilePollQueue $profilePollQueueModel,
		\Psr\Log\LoggerInterface $logger
	)
	{
		$this->atlas = $atlas;
		$this->profilePollQueueModel = $profilePollQueueModel;
		$this->logger = $logger;
	}

	public function execute(Request $request, Response $response): Response
	{
		try {
			$hexUrl = filter_input(INPUT_GET, 'url');
			if (!$hexUrl) {
				throw new \Exception('Missing url GET parameter', 400);
			}

			$url = strtolower(hex2bin($hexUrl));

			$this->logger->info('Received profile URL: ' . $url);

			$host = parse_url($url, PHP_URL_HOST);
			if (!$host) {
				$this->logger->warning('Missing hostname in received profile URL: ' . $url);
				throw new \Exception('Missing hostname', 400);
			}

			if (!\Friendica\Directory\Utils\Network::isPublicHost($host)) {
				$this->logger->warning('Private/reserved IP in received profile URL: ' . $url);
				throw new \Exception('Private/reserved hostname', 400);
			}

			$profileUriInfo = \Friendica\Directory\Models\Profile::extractInfoFromProfileUrl($url);
			if (!$profileUriInfo) {
				$this->logger->warning('Invalid received profile URL: ' . $url);
				throw new \Exception('Invalid Profile URL', 400);
			}

			$this->atlas->perform(
				'INSERT INTO `server_poll_queue` SET `base_url` = :base_url ON DUPLICATE KEY UPDATE `request_count` = `request_count` + 1',
				['base_url' => $profileUriInfo['server_uri']]
			);

			$this->profilePollQueueModel->add($url);

			$this->logger->info('Successfully received profile URL');
		} catch (\Exception $ex) {
			$response = $response->withStatus($ex->getCode(), $ex->getMessage());
		}

		return $response;
	}
}
