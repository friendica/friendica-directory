<?php

namespace Friendica\Directory\Models;

use Friendica\Directory\Utils\Network;

/**
 * @author Hypolite Petovan <mrpetovan@gmail.com>
 */
class ProfilePollQueue extends \Friendica\Directory\Model
{
	const EMPTY_URL      = 1;
	const MISSING_HOST   = 2;
	const PRIVATE_HOST   = 3;
	const ALREADY_EXISTS = 4;

	/**
	 * @param string $profile_url
	 * @return int 0 on success or error code
	 */
	public function add(string $profile_url): int
	{
		$url = trim($profile_url);

		if (!$url) {
			return self::EMPTY_URL;
		}

		$host = parse_url($url, PHP_URL_HOST);
		if (!$host) {
			return self::MISSING_HOST;
		}

		if (!Network::isPublicHost($host)) {
			return self::PRIVATE_HOST;
		}

		$affected = $this->atlas->fetchAffected(
			'INSERT IGNORE INTO `profile_poll_queue` SET `profile_url` = :profile_url',
			['profile_url' => $url]
		);

		return ($affected == 1 ? 0 : self::ALREADY_EXISTS);
	}
}
