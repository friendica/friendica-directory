<?php

namespace Friendica\Directory\Controllers\Api;

use Friendica\Directory\Content\Pager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @author Hypolite Petovan <mrpetovan@gmail.com>
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

		$values = ['query' => $query];

		$profiles = $this->profileModel->getListForDisplay($pager->getItemsPerPage(), $pager->getStart(), $sql_where, $values);

		$count = $this->profileModel->getCountForDisplay($sql_where, $values);

		$vars = [
			'query'        => $query,
			'page'         => $pager->getPage(),
			'itemsperpage' => $pager->getItemsPerPage(),
			'count'        => $count,
			'profiles'     => $profiles
		];

		return $response->withJson($vars);
	}
}
