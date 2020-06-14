<?php

namespace Friendica\Directory\Utils;

/**
 * URL related static utilities
 *
 * @author Hypolite Petovan <hypolite@mrpetovan.com>
 * @package Friendica\Directory\Utils
 */
class Url
{
	/**
	 * Mirror of parse_url function, puts components back together to form a URI.
	 *
	 * @param array $parsed
	 * @return string
	 */
	public static function unparse(array $parsed)
	{
		$scheme    = $parsed['scheme'] ?? null;
		$user      = $parsed['user'] ?? null;
		$pass      = $parsed['pass'] ?? null;
		$userinfo  = $pass !== null ? "$user:$pass" : $user;
		$port      = $parsed['port'] ?? null;
		$query     = $parsed['query'] ?? null;
		$fragment  = $parsed['fragment'] ?? null;
		$authority = ($userinfo !== null ? $userinfo . "@" : '') .
			($parsed['host'] ?? '') .
			($port ? ":$port" : '');

		return	(!empty($scheme) ? $scheme . ":" : '') .
			(strlen($authority) ? "//" . $authority : '') .
			($parsed['path'] ?? '') .
			(strlen($query) ? "?" . $query : '') .
			(strlen($fragment) ? "#" . $fragment : '');
	}
}
