<?php

namespace Friendica\Directory\Utils;

/**
 * @author Hypolite Petovan <mrpetovan@gmail.com>
 */
class Scrape
{

	/**
	 * @param string $url
	 * @return array|false
	 */
	public static function retrieveNoScrapeData(string $url)
	{
		$submit_noscrape_start = microtime(true);
		$data = Network::fetchURL($url);
		$submit_noscrape_request_end = microtime(true);

		if (empty($data)) {
			return false;
		}

		$params = json_decode($data, true);
		if (!$params || !count($params)) {
			return false;
		}

		if (isset($params['tags'])) {
			$params['tags'] = implode(' ', (array)$params['tags']);
		} else {
			$params['tags'] = '';
		}

		$submit_noscrape_end = microtime(true);
		$params['_timings'] = array(
			'fetch' => round(($submit_noscrape_request_end - $submit_noscrape_start) * 1000),
			'scrape' => round(($submit_noscrape_end - $submit_noscrape_request_end) * 1000)
		);

		return $params;
	}

	public static function retrieveProfileData(string $url, int $max_nodes = 3500): array
	{

		$minNodes = 100; //Lets do at least 100 nodes per type.
		$timeout = 10; //Timeout will affect batch processing.

		//Try and cheat our way into faster profiles.
		if (strpos($url, 'tab=profile') === false) {
			$url .= (strpos($url, '?') > 0 ? '&' : '?') . 'tab=profile';
		}

		$scrape_start = microtime(true);

		$params = [];
		$html = Network::fetchURL($url, false, $timeout);

		$scrape_fetch_end = microtime(true);

		if (!$html) {
			return $params;
		}

		$html5 = new \Masterminds\HTML5();
		$dom = $html5->loadHTML($html);

		if (!$dom) {
			return $params;
		}

		$items = $dom->getElementsByTagName('meta');

		// get DFRN link elements
		$nodes_left = max(intval($max_nodes), $minNodes);
		$targets = array('hide', 'comm', 'tags');
		$targets_left = count($targets);
		foreach ($items as $item) {
			$meta_name = $item->getAttribute('name');
			if ($meta_name == 'dfrn-global-visibility') {
				$z = strtolower(trim($item->getAttribute('content')));
				if ($z != 'true') {
					$params['hide'] = 1;
				}
				if ($z === 'false') {
					$params['explicit-hide'] = 1;
				}
				$targets_left = self::popScrapeTarget($targets, 'hide');
			}
			if ($meta_name == 'friendika.community' || $meta_name == 'friendica.community') {
				$z = strtolower(trim($item->getAttribute('content')));
				if ($z == 'true') {
					$params['comm'] = 1;
				}
				$targets_left = self::popScrapeTarget($targets, 'comm');
			}
			if ($meta_name == 'keywords') {
				$z = str_replace(',', ' ', strtolower(trim($item->getAttribute('content'))));
				if (strlen($z)) {
					$params['tags'] = $z;
				}
				$targets_left = self::popScrapeTarget($targets, 'tags');
			}
			$nodes_left--;
			if ($nodes_left <= 0 || $targets_left <= 0) {
				break;
			}
		}

		$items = $dom->getElementsByTagName('link');

		// get DFRN link elements

		$nodes_left = max(intval($max_nodes), $minNodes);
		foreach ($items as $item) {
			$link_rel = $item->getAttribute('rel');
			if (substr($link_rel, 0, 5) == "dfrn-") {
				$params[$link_rel] = $item->getAttribute('href');
			}
			$nodes_left--;
			if ($nodes_left <= 0) {
				break;
			}
		}

		// Pull out hCard profile elements

		$nodes_left = max(intval($max_nodes), $minNodes);
		$items = $dom->getElementsByTagName('*');
		$targets = array('fn', 'pdesc', 'photo', 'key', 'locality', 'region', 'postal-code', 'country-name');
		$targets_left = count($targets);
		foreach ($items as $item) {
			if (self::attributeContains($item->getAttribute('class'), 'vcard')) {
				$level2 = $item->getElementsByTagName('*');
				foreach ($level2 as $vcard_element) {
					if (self::attributeContains($vcard_element->getAttribute('class'), 'fn')) {
						$params['fn'] = $vcard_element->textContent;
						$targets_left = self::popScrapeTarget($targets, 'fn');
					}
					if (self::attributeContains($vcard_element->getAttribute('class'), 'title')) {
						$params['pdesc'] = $vcard_element->textContent;
						$targets_left = self::popScrapeTarget($targets, 'pdesc');
					}
					if (self::attributeContains($vcard_element->getAttribute('class'), 'photo')) {
						$params['photo'] = $vcard_element->getAttribute('src');
						$targets_left = self::popScrapeTarget($targets, 'photo');
					}
					if (self::attributeContains($vcard_element->getAttribute('class'), 'key')) {
						$params['key'] = $vcard_element->textContent;
						$targets_left = self::popScrapeTarget($targets, 'key');
					}
					if (self::attributeContains($vcard_element->getAttribute('class'), 'locality')) {
						$params['locality'] = $vcard_element->textContent;
						$targets_left = self::popScrapeTarget($targets, 'locality');
					}
					if (self::attributeContains($vcard_element->getAttribute('class'), 'region')) {
						$params['region'] = $vcard_element->textContent;
						$targets_left = self::popScrapeTarget($targets, 'region');
					}
					if (self::attributeContains($vcard_element->getAttribute('class'), 'postal-code')) {
						$params['postal-code'] = $vcard_element->textContent;
						$targets_left = self::popScrapeTarget($targets, 'postal-code');
					}
					if (self::attributeContains($vcard_element->getAttribute('class'), 'country-name')) {
						$params['country-name'] = $vcard_element->textContent;
						$targets_left = self::popScrapeTarget($targets, 'country-name');
					}
				}
			}
			$nodes_left--;
			if ($nodes_left <= 0 || $targets_left <= 0) {
				break;
			}
		}

		$scrape_end = microtime(true);
		$fetch_time = round(($scrape_fetch_end - $scrape_start) * 1000);
		$scrape_time = round(($scrape_end - $scrape_fetch_end) * 1000);

		$params['_timings'] = array(
			'fetch' => $fetch_time,
			'scrape' => $scrape_time
		);

		return $params;
	}

	private static function attributeContains(string $attr, string $s): bool
	{
		$a = explode(' ', $attr);
		return count($a) && in_array($s, $a);
	}

	private static function popScrapeTarget(array &$array, string $name): int
	{
		$at = array_search($name, $array);
		unset($array[$at]);
		return count($array);
	}

}
