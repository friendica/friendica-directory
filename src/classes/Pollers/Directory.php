<?php

namespace Friendica\Directory\Pollers;

use Friendica\Directory\Utils\Network;

/**
 * @author Hypolite Petovan <hypolite@mrpetovan.com>
 */
class Directory
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

	/**
	 * @var array
	 */
	private $settings = [
		'probe_timeout' => 5
	];

	public function __construct(
		\Atlas\Pdo\Connection $atlas,
		\Friendica\Directory\Models\ProfilePollQueue $profilePollQueueModel,
		\Psr\Log\LoggerInterface $logger,
		array $settings)
	{
		$this->atlas = $atlas;
		$this->profilePollQueueModel = $profilePollQueueModel;
		$this->logger = $logger;
		$this->settings = array_merge($this->settings, $settings);
	}

	/**
	 * @param string   $directory_url
	 * @param int|null $last_polled
	 * @return bool
	 */
	public function __invoke(string $directory_url, int $last_polled = null): bool
	{
		$this->logger->info('Pull from directory with URL: ' . $directory_url);

		try {
			$host = parse_url($directory_url, PHP_URL_HOST);
			if (!$host) {
				throw new \Exception('Missing hostname in polled directory URL: ' . $directory_url);
			}

			if (!\Friendica\Directory\Utils\Network::isPublicHost($host)) {
				throw new \Exception('Private/reserved IP in polled directory URL: ' . $directory_url);
			}

			$profiles = $this->getPullResult($directory_url, $last_polled);
			foreach ($profiles as $profile_url) {
				$result = $this->profilePollQueueModel->add($profile_url);
				$this->logger->debug('Profile queue add URL: ' . $profile_url . ' - ' . $result);
			}

			$this->logger->info('Successfully pulled ' . count($profiles) . ' profiles');

			return true;
		} catch (\Exception $e) {
			$this->logger->warning($e->getMessage());
			return false;
		}
	}

	private function getPullResult(string $directory_url, ?int $last_polled = null): array
	{
		$path = '/sync/pull/all';
		if ($last_polled) {
			$path = '/sync/pull/since/' . $last_polled;
		}

		//Prepare the CURL call.
		$handle = curl_init();
		$options = array(
			//Timeouts
			CURLOPT_TIMEOUT => max($this->settings['probe_timeout'], 1), //Minimum of 1 second timeout.
			CURLOPT_CONNECTTIMEOUT => 1,
			//Redirecting
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_MAXREDIRS => 8,
			//SSL
			CURLOPT_SSL_VERIFYPEER => true,
			// CURLOPT_VERBOSE => true,
			// CURLOPT_CERTINFO => true,
			CURLOPT_SSL_VERIFYHOST => 2,
			CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
			//Basic request
			CURLOPT_USERAGENT => Network::USER_AGENT,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_URL => $directory_url . $path
		);
		curl_setopt_array($handle, $options);

		$this->logger->info('Pulling profiles from directory URL: ' . $directory_url . $path);

		//Probe the site.
		$pull_data = curl_exec($handle);

		//Done with CURL now.
		curl_close($handle);

		$data = json_decode($pull_data, true);

		if (!isset($data['results']) || !is_array($data['results'])) {
			throw new \Exception('Invalid directory pull data for directory with URL: ' . $directory_url . $path);
		}

		return $data['results'];
	}
}
