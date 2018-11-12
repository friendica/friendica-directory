<?php

namespace Friendica\Directory\Models;

/**
 * @author Hypolite Petovan <mrpetovan@gmail.com>
 */
class Server extends \Friendica\Directory\Model
{
	/**
	 * @param string $server_url
	 * @return array|null
	 */
	public function getByUrlAlias(string $server_url): ?array
	{
		$server_alias = str_replace(['http://', 'https://'], ['', ''], $server_url);

		$server = $this->atlas->fetchOne('SELECT s.* FROM `server` s JOIN `server_alias` sa ON sa.`server_id` = s.`id` WHERE sa.`alias` = :alias',
			['alias' => $server_alias]
		);

		return $server;
	}

	/**
	 * @param string $server_url
	 */
	public function addAliasToServer(int $server_id, string $server_url): void
	{
		$server_alias = str_replace(['http://', 'https://'], ['', ''], $server_url);

		$this->atlas->perform('INSERT INTO `server_alias`
			SET `server_id` = :server_id,
				`alias` = :alias,
				`timestamp` = NOW()
			ON DUPLICATE KEY UPDATE `timestamp` = NOW()',
			[
				'server_id' => $server_id,
				'alias' => strtolower($server_alias)
			]);
	}
}
