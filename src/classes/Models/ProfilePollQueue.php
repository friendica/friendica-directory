<?php

namespace Friendica\Directory\Models;

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

		$this->atlas->perform(
			'INSERT IGNORE INTO `profile_poll_queue` SET `profile_url` = :profile_url',
			['profile_url' => $url]
		);

		return true;
	}
}
