<?php

namespace Friendica\Directory\Controllers;

/**
 * @author Hypolite Petovan <mrpetovan@gmail.com>
 */
class Cron
{
	/**
	 * @var \Psr\Log\LoggerInterface
	 */
	protected $logger;

	/**
	 * @var \Friendica\Directory\Pollers\Profile
	 */
	protected $profilePoller;

	/**
	 * @var \Friendica\Directory\Pollers\Server
	 */
	protected $serverPoller;

	/**
	 * @var \Friendica\Directory\Pollers\Directory
	 */
	protected $directoryPoller;

	/**
	 * @var \Atlas\Pdo\Connection
	 */
	protected $atlas;

	/**
	 * @var array
	 */
	protected $settings = [
		'directory_poll_delay' => 3600, // 1 hour
		'server_poll_delay' => 24 * 3600, // 1 day
		'profile_poll_delay' => 24 * 3600, // 1 day

		'directory_poll_retry_base_delay' => 600, // 10 minutes
		'server_poll_retry_base_delay' => 1800, // 30 minutes
		'profile_poll_retry_base_delay' => 1800, // 30 minutes
	];

	/**
	 * @var float
	 */
	private $startTime;

	public function __construct(
		\Atlas\Pdo\Connection $atlas,
		\Friendica\Directory\Pollers\Profile $profilePoller,
		\Friendica\Directory\Pollers\Server $serverPoller,
		\Friendica\Directory\Pollers\Directory $directoryPoller,
		\Psr\Log\LoggerInterface $logger,
		array $settings = []
	)
	{
		$this->atlas = $atlas;
		$this->profilePoller = $profilePoller;
		$this->serverPoller = $serverPoller;
		$this->directoryPoller = $directoryPoller;
		$this->logger = $logger;
		$this->settings = array_merge($this->settings, $settings);
		$this->startTime = microtime(true);
	}

	public function execute()
	{
		$this->logger->info('Start Cron job');

		$this->pollDirectories(9);

		$this->pollServers(24);

		$this->pollProfiles(58);

		$this->logger->info('Stop Cron job');
	}

	/**
	 * @param int|null $time_limit
	 */
	private function pollDirectories(int $time_limit = null): void
	{
		$directories = $this->atlas->fetchAll(
			'SELECT `directory_url`, `retries_count`
			FROM `directory_poll_queue`
			WHERE `next_poll` <= NOW()
			ORDER BY ISNULL(`last_polled`) DESC'
		);

		$this->logger->info('Directories polling queue size: ' . count($directories));

		foreach ($directories as $directory) {
			if ($time_limit && microtime(true) - $this->startTime > $time_limit) {
				break;
			}

			$directory_poll_result = $this->directoryPoller->__invoke($directory['directory_url']);

			if ($directory_poll_result) {
				$new_retries_count = 0;
				$poll_delay = $this->settings['directory_poll_delay'];
			} else {
				$new_retries_count = $directory['retries_count'] + 1;
				$poll_delay = $this->settings['directory_poll_retry_base_delay'] * pow($new_retries_count, 3);
			}

			$this->atlas->perform(
				'UPDATE `directory_poll_queue` SET
					`last_polled` = NOW(),
					`next_poll` = DATE_ADD(NOW(), INTERVAL :seconds SECOND),
					`retries_count` = :retries_count
				WHERE `directory_url` = :directory_url',
				[
					'seconds' => [$poll_delay, \PDO::PARAM_INT],
					'directory_url' => $directory['directory_url'],
					'retries_count' => [$new_retries_count, \PDO::PARAM_INT]
				]
			);

		}
	}

	private function pollServers(int $time_limit = null): void
	{
		$servers = $this->atlas->fetchAll(
			'SELECT `base_url`, `retries_count`
			FROM `server_poll_queue`
			WHERE `next_poll` <= NOW()
			ORDER BY ISNULL(`last_polled`) DESC, `request_count` DESC'
		);

		$this->logger->info('Servers polling queue size: ' . count($servers));

		foreach ($servers as $server_queue_item) {
			if ($time_limit && microtime(true) - $this->startTime > $time_limit) {
				break;
			}

			try {
				$new_base_url = null;

				$server_id = $this->serverPoller->__invoke($server_queue_item['base_url']);

				if ($server_id) {
					$new_base_url = $this->atlas->fetchValue('SELECT `base_url` FROM `server` WHERE `id` = :id', ['id' => [$server_id, \PDO::PARAM_INT]]);
				}

				if ($new_base_url && $new_base_url != $server_queue_item['base_url']) {
					$this->atlas->perform('INSERT IGNORE INTO `server_poll_queue` SET `base_url` = :base_url', ['base_url' => $new_base_url]);
					$this->logger->info('New base URL: ' . $server_queue_item['base_url'] . ' => ' . $new_base_url);
				}

				if ($new_base_url == $server_queue_item['base_url']) {
					$new_retries_count = 0;
					$poll_delay = $this->settings['server_poll_delay'];
				} else {
					$new_retries_count = $server_queue_item['retries_count'] + 1;
					$poll_delay = $this->settings['server_poll_retry_base_delay'] * pow($new_retries_count, 3);
				}

				$this->atlas->perform(
					'UPDATE `server_poll_queue` SET
						`last_polled` = NOW(),
						`next_poll` = DATE_ADD(NOW(), INTERVAL :seconds SECOND),
						`retries_count` = :retries_count,
						`request_count` = 0
					WHERE `base_url` = :base_url',
					[
						'seconds' => [$poll_delay, \PDO::PARAM_INT],
						'base_url' => $server_queue_item['base_url'],
						'retries_count' => [$new_retries_count, \PDO::PARAM_INT]
					]
				);
			} catch (\Exception $e) {
				$this->logger->error($e->getMessage() . ': ' . $e->getTraceAsString());
			}
		}
	}

	private function pollProfiles(int $time_limit = null): void
	{
		$profiles = $this->atlas->fetchAll(
			'SELECT `profile_url`, `retries_count`
			FROM `profile_poll_queue`
			WHERE `next_poll` <= NOW()
			ORDER BY RAND() ASC'
		);

		$this->logger->info('Profiles polling queue size: ' . count($profiles));

		foreach ($profiles as $profile) {
			if ($time_limit && microtime(true) - $this->startTime > $time_limit) {
				break;
			}

			try {
				$profile_poll_result = $this->profilePoller->__invoke($profile['profile_url']);

				if ($profile_poll_result) {
					$new_retries_count = 0;
					$poll_delay = $this->settings['profile_poll_delay'];
				} else {
					$new_retries_count = $profile['retries_count'] + 1;
					$poll_delay = $this->settings['profile_poll_retry_base_delay'] * pow($new_retries_count, 3);
				}


				$this->atlas->perform('UPDATE `profile_poll_queue` SET
						`last_polled` = NOW(),
						`next_poll` = DATE_ADD(NOW(), INTERVAL :seconds SECOND),
						`retries_count` = :retries_count
					WHERE `profile_url` = :profile_url',
					[
						'seconds' => [$poll_delay, \PDO::PARAM_INT],
						'profile_url' => $profile['profile_url'],
						'retries_count' => [$new_retries_count, \PDO::PARAM_INT]
					]
				);
			} catch (\Exception $e) {
				$this->logger->error($e->getMessage() . ': ' . $e->getTraceAsString());
			}
		}
	}
}
