<?php

namespace Friendica\Directory\Models;

use Friendica\Directory\Utils\Network;

/**
 * @author Hypolite Petovan <mrpetovan@gmail.com>
 */
class ProfilePollQueue extends \Friendica\Directory\Model
{
	public function add(string $profile_url): bool
	{
		$url = trim($profile_url);

		if (!$url) {
			return false;
		}

		$host = parse_url($url, PHP_URL_HOST);
		if (!$host) {
			return false;
		}

		if (Network::isPublicHost($host)) {
			return false;
		}

		$this->atlas->perform(
			'INSERT IGNORE INTO `profile_poll_queue` SET `profile_url` = :profile_url',
			['profile_url' => $url]
		);

		return true;
	}
}
