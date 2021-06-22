<?php

namespace Friendica\Directory\Controllers\Api;

use Friendica\Directory\Content\Pager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @author Hypolite Petovan <hypolite@mrpetovan.com>
 */
class MatchSearch
{
	/**
	 * @var \Atlas\Pdo\Connection
	 */
	private $atlas;
	/**
	 * @var \Friendica\Directory\Models\Profile
	 */
	private $profileModel;
	/**
	 * @var \Gettext\TranslatorInterface
	 */
	private $l10n;

	public function __construct(
		\Atlas\Pdo\Connection $atlas,
		\Friendica\Directory\Models\Profile $profileModel,
		\Gettext\TranslatorInterface $l10n
	)
	{
		$this->atlas = $atlas;
		$this->profileModel = $profileModel;
		$this->l10n = $l10n;
	}

	public function render(\Slim\Http\Request $request, \Slim\Http\Response $response, array $args): \Slim\Http\Response
	{
		$perpage = filter_input(INPUT_POST, 'n', FILTER_SANITIZE_NUMBER_INT);
		$query  = filter_input(INPUT_POST, 's', FILTER_SANITIZE_STRING);

		if (!$perpage) {
			$perpage = 80;
		}

		$pager = new Pager($this->l10n, $request, $perpage);
		$pager->setPage(filter_input(INPUT_POST, 'p', FILTER_SANITIZE_NUMBER_INT));

		$sql_where = "MATCH (p.`tags`) AGAINST (:query)";
		// At sign (@) is a reserved symbol in InnoDB full-text search, it can't be escaped
		$query = str_replace('@', ' ', $query);

		$values = ['query' => $query];

		$profiles = $this->profileModel->getListForDisplay($pager->getItemsPerPage(), $pager->getStart(), $sql_where, $values);

		$results = [];
		foreach ($profiles as $profile) {
			$results[] = [
				'name'  => $profile['name'],
				'url'   => $profile['profile_url'],
				'photo' => $profile['photo'],
				'tags'  => $profile['tags'],
			];
		}

		$count = $this->profileModel->getCountForDisplay($sql_where, $values);

		$vars = [
			'query'      => $query,
			'page'       => $pager->getPage(),
			'items_page' => $pager->getItemsPerPage(),
			'total'      => $count,
			'results'    => $results
		];

		return $response->withJson($vars);
	}
}
