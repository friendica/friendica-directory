<?php

namespace Friendica\Directory\Pollers;

/**
 * @author Hypolite Petovan <mrpetovan@gmail.com>
 */
class Server
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
	 * @var \Friendica\Directory\Models\Server
	 */
	private $serverModel;
	/**
	 * @var \Psr\SimpleCache\CacheInterface
	 */
	private $simplecache;

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
		\Friendica\Directory\Models\Server $serverModel,
		\Psr\SimpleCache\CacheInterface $simplecache,
		\Psr\Log\LoggerInterface $logger,
		array $settings)
	{
		$this->atlas = $atlas;
		$this->profilePollQueueModel = $profilePollQueueModel;
		$this->serverModel = $serverModel;
		$this->simplecache = $simplecache;
		$this->logger = $logger;
		$this->settings = array_merge($this->settings, $settings);
	}

	public function __invoke(string $polled_url): int
	{
		$this->logger->info('Poll server with URL: ' . $polled_url);

		$host = parse_url($polled_url, PHP_URL_HOST);

		if (!$host) {
			$this->logger->warning('Missing hostname in polled server URL: ' . $polled_url);
			return 0;
		}

		if (!\Friendica\Directory\Utils\Network::isPublicHost($host)) {
			$this->logger->warning('Private/reserved IP in polled server URL: ' . $polled_url);
			return 0;
		}

		$server = $this->serverModel->getByUrlAlias($polled_url);

		if (
			$server
			&& substr($polled_url, 0, 7) == 'http://'
			&& substr($server['base_url'], 0, 8) == 'https://'
		) {
			$this->logger->info('Favoring the HTTPS version of server with URL: ' . $polled_url);
			return $server['id'];
		}

		if ($server) {
			$this->atlas->perform('UPDATE `server` SET `available` = 0 WHERE `id` = :server_id', ['server_id' => $server['id']]);
		}

		$probe_result = $this->getProbeResult($polled_url);

		$parse_success = !empty($probe_result['data']);

		if ($parse_success) {
			$base_url = $probe_result['data']['url'];

			// Maybe we know the server under the canonical URL?
			if (!$server) {
				$server = $this->serverModel->getByUrlAlias($base_url);
			}

			if (!$server) {
				$this->atlas->perform('INSERT INTO `server` SET
					`base_url` = :base_url,
					`first_noticed` = NOW(),
					`available` = 0,
					`health_score` = 50',
					['base_url' => $polled_url]
				);

				$server = [
					'id' => $this->atlas->lastInsertId(),
					'base_url' => $base_url,
					'health_score' => 50
				];
			}

			$this->serverModel->addAliasToServer($server['id'], $polled_url);
			$this->serverModel->addAliasToServer($server['id'], $base_url);

			$avg_ping = $this->getAvgPing($base_url);
			if ($probe_result['time'] && $avg_ping) {
				$speed_score = max(1, $avg_ping > 10 ? $probe_result['time'] / $avg_ping : $probe_result['time'] / 50);
			} else {
				$speed_score = null;
			}

			$this->atlas->perform('INSERT INTO `site_probe`
				SET `server_id`    = :server_id,
					`request_time` = :request_time,
					`avg_ping`     = :avg_ping,
					`speed_score`  = :speed_score,
					`timestamp`    = NOW()',
				[
					'server_id' => $server['id'],
					'request_time' => $probe_result['time'],
					'avg_ping' => $avg_ping,
					'speed_score' => $speed_score
				]
			);

			if (isset($probe_result['data']['addons'])) {
				$addons = $probe_result['data']['addons'];
			} else {
				// Backward compatibility
				$addons = $probe_result['data']['plugins'];
			}

			$this->atlas->perform(
				'UPDATE `server`
				SET `available`     = 1,
					`last_seen`     = NOW(),
					`base_url`      = :base_url,
					`name`          = :name,
					`version`       = :version,
					`addons`        = :addons,
					`reg_policy`    = :reg_policy,
					`info`          = :info,
					`admin_name`    = :admin_name,
					`admin_profile` = :admin_profile,
					`noscrape_url`  = :noscrape_url,
					`ssl_state`     = :ssl_state
				WHERE `id` = :server_id',
				[
					'server_id' => $server['id'],
					'base_url' => strtolower($probe_result['data']['url']),
					'name' => $probe_result['data']['site_name'],
					'version' => $probe_result['data']['version'],
					'addons' => implode(',', $addons),
					'reg_policy' => $probe_result['data']['register_policy'],
					'info' => $probe_result['data']['info'],
					'admin_name' => $probe_result['data']['admin']['name'],
					'admin_profile' => $probe_result['data']['admin']['profile'],
					'noscrape_url' => $probe_result['data']['no_scrape_url'] ?? null,
					'ssl_state' => $probe_result['ssl_state']
				]
			);

			//Add the admin to the directory
			if (!empty($probe_result['data']['admin']['profile'])) {
				$result = $this->profilePollQueueModel->add($probe_result['data']['admin']['profile']);
				$this->logger->debug('Profile queue add URL: ' . $probe_result['data']['admin']['profile'] . ' - ' . $result);
			}
		}

		if ($server) {
			//Get the new health.
			$version = $parse_success ? $probe_result['data']['version'] : '';
			$health_score = $this->computeHealthScore($server['health_score'], $parse_success, $probe_result['time'], $version, $probe_result['ssl_state']);

			$this->atlas->perform(
				'UPDATE `server` SET `health_score` = :health_score WHERE `id` = :server_id',
				['health_score' => $health_score, 'server_id' => $server['id']]
			);
		}

		if ($parse_success) {
			$this->logger->info('Server poll successful');
		} else {
			$this->logger->info('Server poll unsuccessful');
		}

		return $parse_success ? $server['id'] : 0;
	}

	/**
	 * @param string $base_url
	 * @return float|null
	 */
	private function getAvgPing(string $base_url)
	{
		$net_ping = \Net_Ping::factory();
		$net_ping->setArgs(['count' => 5]);
		$ping_result = $net_ping->ping(parse_url($base_url, PHP_URL_HOST));

		if (is_a($ping_result, 'Net_Ping_Result')) {
			$avg_ping = $ping_result->getAvg();
		} else {
			$avg_ping = null;
		}

		unset($net_ping);

		return $avg_ping;
	}

	private function getProbeResult(string $base_url): array
	{
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
			CURLOPT_USERAGENT => 'friendica-directory-probe-1.0',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_URL => $base_url . '/friendica/json'
		);
		curl_setopt_array($handle, $options);

		//Probe the site.
		$probe_start = microtime(true);
		$probe_data = curl_exec($handle);
		$probe_end = microtime(true);

		//Check for SSL problems.
		$curl_statuscode = curl_errno($handle);
		$sslcert_issues = in_array($curl_statuscode, array(
			60, //Could not authenticate certificate with known CA's
			83  //Issuer check failed
		));

		//When it's the certificate that doesn't work.
		if ($sslcert_issues) {
			//Probe again, without strict SSL.
			$options[CURLOPT_SSL_VERIFYPEER] = false;

			//Replace the handle.
			curl_close($handle);
			$handle = curl_init();
			curl_setopt_array($handle, $options);

			//Probe.
			$probe_start = microtime(true);
			$probe_data = curl_exec($handle);
			$probe_end = microtime(true);

			//Store new status.
			$curl_statuscode = curl_errno($handle);
		}

		//Gather more meta.
		$time = round(($probe_end - $probe_start) * 1000);
		$curl_info = curl_getinfo($handle);

		//Done with CURL now.
		curl_close($handle);

		try {
			$data = json_decode($probe_data, true);
		} catch (\Exception $ex) {
			$data = false;
		}

		$ssl_state = 0;
		if (parse_url($base_url, PHP_URL_SCHEME) == 'https') {
			if ($sslcert_issues) {
				$ssl_state = -1;
			} else {
				$ssl_state = 1;
			}
		}

		return ['data' => $data, 'time' => $time, 'curl_info' => $curl_info, 'ssl_state' => $ssl_state];
	}

	private function computeHealthScore(int $original_health, bool $probe_success, int $time = null, string $version = null, int $ssl_state = null): int
	{
		//Probe failed, costs you 30 points.
		if (!$probe_success) {
			return max($original_health - 30, -100);
		}

		//A good probe gives you 10 points.
		$delta = 10;
		$max_health = 100;

		//Speed scoring.
		if (intval($time) > 0) {
			//Penalty / bonus points.
			if ($time > 800) {
				$delta -= 10; //Bad speed.
			} elseif ($time > 400) {
				$delta -= 5; //Still not good.
			} elseif ($time > 250) {
				$delta += 0; //This is normal.
			} elseif ($time > 120) {
				$delta += 5; //Good speed.
			} else {
				$delta += 10; //Excellent speed.
			}

			//Cap for bad speeds.
			if ($time > 800) {
				$max_health = 40;
			} elseif ($time > 400) {
				$max_health = 60;
			}
		}

		if ($ssl_state == 1) {
			$delta += 10;
		} elseif ($ssl_state == -1) {
			$delta -= 10;
		}

		//Version check.
		if (!empty($version)) {
			$versionParts = explode('.', $version);

			if (intval($versionParts[0]) == 3) {
				$max_health = 30; // Really old version
			} else {
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

				if ($version == $dev_version) {
					$max_health = 95; //Develop can be unstable
				} elseif ($version !== $stable_version) {
					$delta = min($delta, 0) - 10; // Losing score as time passes if node isn't updated
				}
			}
		}

		return max(min($max_health, $original_health + $delta), -100);
	}
}
