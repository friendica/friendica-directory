<?php

namespace Friendica\Directory\Models;

/**
 * @author Hypolite Petovan <mrpetovan@gmail.com>
 */
class Profile extends \Friendica\Directory\Model
{
	public function deleteById(int $profile_id): bool
	{
		$this->atlas->perform('DELETE FROM `photo` WHERE `profile_id` = :profile_id',
			['profile_id' => [$profile_id, \PDO::PARAM_INT]]
		);
		$this->atlas->perform('DELETE FROM `tag` WHERE `profile_id` = :profile_id',
			['profile_id' => [$profile_id, \PDO::PARAM_INT]]
		);
		$this->atlas->perform('DELETE FROM `profile` WHERE `id` = :profile_id',
			['profile_id' => [$profile_id, \PDO::PARAM_INT]]
		);

		return true;
	}

	/**
	 * @param string $profile_uri
	 * @return array|boolean
	 */
	public static function extractInfoFromProfileUrl(string $profile_uri)
	{
		if (substr($profile_uri, 0, 4) === 'http') {
			// http://friendica.mrpetovan.com/profile/hypolite
			// https://friendica.mrpetovan.com/profile/hypolite
			// http://friendica.mrpetovan.com/~hypolite
			// https://friendica.mrpetovan.com/~hypolite

			$username = ltrim(basename($profile_uri), '~');

			if (strpos($profile_uri, '~') !== false) {
				$server_uri = substr($profile_uri, 0, strpos($profile_uri, '/~'));
			} elseif (strpos($profile_uri, '/profile/') !== false) {
				$server_uri = substr($profile_uri, 0, strpos($profile_uri, '/profile/'));
			} else {
				return false;
			}
		} else {
			// hypolite@friendica.mrpetovan.com
			// acct:hypolite@friendica.mrpetovan.com
			// acct://hypolite@friendica.mrpetovan.com

			$local = str_replace('acct:', '', $profile_uri);

			if (substr($local, 0, 2) == '//') {
				$local = substr($local, 2);
			}

			if (strpos($local, '@') !== false) {
				$username = substr($local, 0, strpos($local, '@'));
				$server_uri = 'http://' . substr($local, strpos($local, '@') + 1);
			} else {
				return false;
			}
		}

		$hostname = str_replace(['https://', 'http://'], ['', ''], $server_uri);

		$addr = $username . '@' . $hostname;

		return [
			'username' => $username,
			'server_uri' => $server_uri,
			'hostname' => $hostname,
			'addr' => $addr
		];
	}

	public function getListForDisplay(int $limit = 30, int $start = 0, string $condition = '', array $values = []): array
	{
		if ($condition) {
			$condition = 'AND ' . $condition;
		}

		$values = array_merge($values, [
			'start' => [$start, \PDO::PARAM_INT],
			'limit' => [$limit, \PDO::PARAM_INT]
		]);

		$stmt = 'SELECT p.`id`, p.`name`, p.`username`, p.`addr`, p.`account_type`, p.`pdesc`,
 				p.`locality`, p.`region`, p.`country`, p.`profile_url`, p.`dfrn_request`, p.`photo`,
 				p.`tags`, p.`last_activity`
			FROM `profile` p
			JOIN `server` s ON s.`id` = p.`server_id` AND s.`available` AND NOT s.`hidden`
			WHERE p.`available`
			AND NOT p.`hidden`
			' . $condition . '
			GROUP BY p.`id`
			ORDER BY `filled_fields` DESC, `last_activity` DESC, `updated` DESC
			LIMIT :start, :limit';
		$profiles = $this->atlas->fetchAll($stmt, $values);

		return $profiles;
	}


	public function getCountForDisplay(string $condition = '', array $values = []): int
	{
		if ($condition) {
			$condition = 'AND ' . $condition;
		}

		$stmt = 'SELECT COUNT(*)
			FROM `profile` p
			JOIN `server` s ON s.`id` = p.`server_id` AND s.`available` AND NOT s.`hidden`
			WHERE p.`available`
			AND NOT p.`hidden`
			' . $condition;
		$count = $this->atlas->fetchValue($stmt, $values);

		return $count;
	}
}
