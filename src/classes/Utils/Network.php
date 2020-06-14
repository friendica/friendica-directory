<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Friendica\Directory\Utils;

/**
 * Description of Network
 *
 * @author Hypolite Petovan <hypolite@mrpetovan.com>
 */
class Network
{
	public static function fetchURL(string $url, bool $binary = false, int $timeout = 20): string
	{
		$ch = curl_init($url);
		if (!$ch) {
			return false;
		}

		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_TIMEOUT, max($timeout, 1)); //Minimum of 1 second timeout.
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 8);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		if ($binary) {
			curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
		}
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$s = curl_exec($ch);
		curl_close($ch);
		return $s;
	}

	public static function testURL(string $url, int $timeout = 20): bool
	{
		$ch = curl_init($url);
		if (!$ch) {
			return false;
		}

		curl_setopt($ch, CURLOPT_HEADER        , 0);
		curl_setopt($ch, CURLOPT_TIMEOUT       , max($timeout, 1)); //Minimum of 1 second timeout.
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_MAXREDIRS     , 8);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($ch, CURLOPT_NOBODY        , true);

		curl_exec($ch);

		$responseCode = intval(curl_getinfo($ch, CURLINFO_RESPONSE_CODE));

		$testSuccess = curl_errno($ch) === 0 && $responseCode < 400;

		curl_close($ch);

		return $testSuccess;
	}

	/**
	 * Check if a hostname is public and non-reserved
	 *
	 * @param  string $host
	 * @return bool
	 */
	public static function isPublicHost(string $host): bool
	{
		if (!$host) {
			return false;
		}

		if ($host === 'localhost') {
			return false;
		}

		// RFC 2606
		if ($host === 'example.com' || $host === 'example.net' || $host === 'example.org') {
			return false;
		}

		// RFC 2606 -continued
		$tld = substr($host, strrpos($host, '.'));
		if ($tld === '.test' || $tld === '.example' || $tld === '.invalid' || $tld === '.localhost') {
			return false;
		}

		// Private/Reserved IP ranges
		if (filter_var($host, FILTER_VALIDATE_IP) && !filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
			return false;
		}

		return true;
	}
}
