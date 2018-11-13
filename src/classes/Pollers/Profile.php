<?php

namespace Friendica\Directory\Pollers;

use Friendica\Directory\Utils\Network;

/**
 * @author Hypolite Petovan <mrpetovan@gmail.com>
 */
class Profile
{

	/**
	 * @var \Atlas\Pdo\Connection
	 */
	private $atlas;

	/**
	 * @var \Friendica\Directory\Models\Server
	 */
	private $serverModel;

	/**
	 * @var \Friendica\Directory\Models\Profile
	 */
	private $profileModel;

	/**
	 * @var \Psr\Log\LoggerInterface
	 */
	private $logger;

	/**
	 * @var array
	 */
	private $settings = [
		'probe_timeout' => 5,
		'remove_profile_health_threshold' => -60
	];

	public function __construct(
		\Atlas\Pdo\Connection $atlas,
		\Friendica\Directory\Models\Server $serverModel,
		\Friendica\Directory\Models\Profile $profileModel,
		\Psr\Log\LoggerInterface $logger,
		array $settings
	)
	{
		$this->atlas = $atlas;
		$this->serverModel = $serverModel;
		$this->profileModel = $profileModel;
		$this->logger = $logger;
		$this->settings = array_merge($this->settings, $settings);
	}

	public function __invoke(string $profile_uri)
	{
		if (!strlen($profile_uri)) {
			$this->logger->error('Received empty profile URI', ['class' => __CLASS__]);
			return false;
		}

		$submit_start = microtime(true);

		$this->logger->info('Poll profile URI: ' . $profile_uri);

		$host = parse_url($profile_uri, PHP_URL_HOST);

		if (!$host) {
			$this->logger->warning('Missing hostname in polled profile URL: ' . $profile_uri);
			return false;
		}

		if (!\Friendica\Directory\Utils\Network::isPublicHost($host)) {
			$this->logger->warning('Private/reserved IP in polled profile URL: ' . $profile_uri);
			return false;
		}

		$profileUriInfo = \Friendica\Directory\Models\Profile::extractInfoFromProfileUrl($profile_uri);
		if (!$profileUriInfo) {
			$this->logger->warning('Profile URI invalid');
			return false;
		}

		$server = $this->serverModel->getByUrlAlias($profileUriInfo['server_uri']);
		if (!$server) {
			$this->atlas->perform('INSERT IGNORE INTO `server_poll_queue` SET `base_url` = :base_url', ['base_url' => $profileUriInfo['server_uri']]);

			// No server entry yet, no need to continue.
			$this->logger->info('Profile poll aborted, no server entry yet for ' . $profileUriInfo['server_uri']);
			return false;
		}

		if ($server['hidden']) {
			$this->logger->info('Profile poll aborted, server is hidden: ' . $server['base_url']);
			return false;
		}

		$username = $profileUriInfo['username'];
		$addr = $profileUriInfo['addr'];

		$profile_id = $this->atlas->fetchValue(
			'SELECT `id` FROM `profile` WHERE `server_id` = :server_id AND `username` = :username',
			['server_id' => $server['id'], 'username' => $username]
		);

		if ($profile_id) {
			$this->atlas->perform(
				'UPDATE `profile` SET
				`available` = 0,
				`updated` = NOW()
				WHERE `id` = :profile_id',
				['profile_id' => [$profile_id, \PDO::PARAM_INT]]
			);

			$this->atlas->perform(
				'DELETE FROM `tag` WHERE `profile_id` = :profile_id',
				['profile_id' => [$profile_id, \PDO::PARAM_INT]]
			);
		}

		//Skip the profile scrape?
		$noscrape = $server['noscrape_url'];

		$params = [];
		if ($noscrape) {
			$this->logger->debug('Calling ' . $server['noscrape_url'] . '/' . $username);
			$params = \Friendica\Directory\Utils\Scrape::retrieveNoScrapeData($server['noscrape_url'] . '/' . $username);
			$noscrape = !!$params; //If the result was false, do a scrape after all.
		}

		$available = true;

		if ($noscrape) {
			$available = Network::testURL($profile_uri);
			$this->logger->debug('Testing ' . $profile_uri . ': ' . ($available?'Success':'Failure'));
		} else {
			$this->logger->notice('Parsing profile page ' . $profile_uri);
			$params = \Friendica\Directory\Utils\Scrape::retrieveProfileData($profile_uri);
		}

		// Empty result is due to an offline site.
		if (count($params) < 2) {
			//But for sites that are already in bad status. Do a cleanup now.
			if ($profile_id && $server['health_score'] < $this->settings['remove_profile_health_threshold']) {
				$this->profileModel->deleteById($profile_id);
			}

			$this->logger->info('Poll aborted, empty result');
			return false;
		} elseif (!empty($params['explicit-hide']) && $profile_id) {
			// We don't care about valid dfrn if the user indicates to be hidden.
			$this->profileModel->deleteById($profile_id);
			$this->logger->info('Poll aborted, profile asked to be removed from directory');
			return true; //This is a good update.
		}

		if (!empty($params['hide']) || empty($params['fn']) || empty($params['photo'])) {
			if ($profile_id) {
				$this->profileModel->deleteById($profile_id);
			}

			if (!empty($params['hide'])) {
				$this->logger->info('Poll aborted, hidden profile.');
			} else {
				$this->logger->info('Poll aborted, incomplete profile.');
			}

			return true; //This is a good update.
		}

		// This is most likely a problem with the site configuration. Ignore.
		if (self::validateParams($params)) {
			$this->logger->warning('Poll aborted, parameters invalid.', ['params' => $params]);
			return false;
		}

		$account_type = 'People';
		if (!empty($params['comm'])) {
			$account_type = 'Forum';
		}

		$tags = [];
		if (!empty($params['tags'])) {
			$incoming_tags = explode(' ', $params['tags']);
			foreach ($incoming_tags as $term) {
				$term = strip_tags(trim($term));
				$term = substr($term, 0, 254);

				$tags[] = $term;
			}

			$tags = array_unique($tags);
		}

		$filled_fields = intval(!empty($params['pdesc'])) * 4 + intval(!empty($params['tags'])) * 2 + intval(!empty($params['locality']) || !empty($params['region']) || !empty($params['country-name']));

		$this->atlas->perform('INSERT INTO `profile` SET
			`id` = :profile_id,
			`server_id` = :server_id,
			`username` = :username,
			`name` = :name,
			`pdesc` = :pdesc,
			`locality` = :locality,
			`region` = :region,
			`country` = :country,
			`profile_url` = :profile_url,
			`dfrn_request` = :dfrn_request,
			`tags` = :tags,
			`addr` = :addr,
			`account_type` = :account_type,
			`filled_fields` = :filled_fields,
			`last_activity` = :last_activity,
			`available` = :available,
			`created` = NOW(),
			`updated` = NOW()
			ON DUPLICATE KEY UPDATE
			`server_id` = :server_id,
			`username` = :username,
			`name` = :name,
			`pdesc` = :pdesc,
			`locality` = :locality,
			`region` = :region,
			`country` = :country,
			`profile_url` = :profile_url,
			`dfrn_request` = :dfrn_request,
			`photo` = :photo,
			`tags` = :tags,
			`addr` = :addr,
			`account_type` = :account_type,
			`filled_fields` = :filled_fields,
			`last_activity` = :last_activity,
			`available` = :available,
			`updated` = NOW()',
			[
				'profile_id' => $profile_id,
				'server_id' => $server['id'],
				'username' => $username,
				'name' => $params['fn'],
				'pdesc' => $params['pdesc'] ?? '',
				'locality' => $params['locality'] ?? '',
				'region' => $params['region'] ?? '',
				'country' => $params['country-name'] ?? '',
				'profile_url' => $profile_uri,
				'dfrn_request' => $params['dfrn-request'] ?? null,
				'photo' => $params['photo'],
				'tags' => implode(' ', $tags),
				'addr' => $addr,
				'account_type' => $account_type,
				'filled_fields' => $filled_fields,
				'last_activity' => $params['last-activity'] ?? null,
				'available' => $available,
			]
		);

		if (!$profile_id) {
			$profile_id = $this->atlas->lastInsertId();
		}

		if (!empty($params['tags'])) {
			$incoming_tags = explode(' ', $params['tags']);
			foreach ($incoming_tags as $term) {
				$term = strip_tags(trim($term));
				$term = substr($term, 0, 254);

				if (strlen($term)) {
					$this->atlas->perform('INSERT IGNORE INTO `tag` (`profile_id`, `term`) VALUES (:profile_id, :term)', ['term' => $term, 'profile_id' => $profile_id]);
				}
			}
		}

		$submit_photo_start = microtime(true);

		$status = false;

		if ($profile_id) {
			$img_str = \Friendica\Directory\Utils\Network::fetchURL($params['photo'], true);
			$img = new \Friendica\Directory\Utils\Photo($img_str);
			if ($img->getImage()) {
				$img->scaleImageSquare(80);

				$this->atlas->perform('INSERT INTO `photo` SET
					`profile_id` = :profile_id,
					`data` = :data
					ON DUPLICATE KEY UPDATE
					`data` = :data',
					[
						'profile_id' => $profile_id,
						'data' => $img->imageString()
					]
				);
			}
			$status = true;
		}

		$submit_end = microtime(true);
		$photo_time = round(($submit_end - $submit_photo_start) * 1000);
		$time = round(($submit_end - $submit_start) * 1000);

		//Record the scrape speed in a scrapes table.
		if ($server && $status) {
			$this->atlas->perform('INSERT INTO `site_scrape` SET
				`server_id` = :server_id,
				`request_time` = :request_time,
				`scrape_time` = :scrape_time,
				`photo_time` = :photo_time,
				`total_time` = :total_time',
				[
					'server_id' => $server['id'],
					'request_time' => $params['_timings']['fetch'],
					'scrape_time' => $params['_timings']['scrape'],
					'photo_time' => $photo_time,
					'total_time' => $time
				]
			);
		}

		$this->logger->info('Profile poll successful');

		return true;
	}

	private static function validateParams(array $params): int
	{
		$errors = 0;
		if (empty($params['key'])) {
			$errors++;
		}
		if (empty($params['dfrn-request'])) {
			$errors++;
		}
		if (empty($params['dfrn-confirm'])) {
			$errors++;
		}
		if (empty($params['dfrn-notify'])) {
			$errors++;
		}
		if (empty($params['dfrn-poll'])) {
			$errors++;
		}

		return $errors;
	}

}
