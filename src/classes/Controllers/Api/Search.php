<?php

namespace Friendica\Directory\Controllers\Api;

use \Friendica\Directory\Content\Pager;
use PDO;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @author Hypolite Petovan <mrpetovan@gmail.com>
 */
class Search
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
	 * @var \Friendica\Directory\Content\L10n
	 */
	private $l10n;

	public function __construct(
		\Atlas\Pdo\Connection $atlas,
		\Friendica\Directory\Models\Profile $profileModel,
		\Friendica\Directory\Content\L10n $l10n
	)
	{
		$this->atlas = $atlas;
		$this->profileModel = $profileModel;
		$this->l10n = $l10n;
	}

	public function render(\Slim\Http\Request $request, \Slim\Http\Response $response, array $args): \Slim\Http\Response
	{
		$pager = new Pager($this->l10n, $request, 20);

		$originalQuery = $query = filter_input(INPUT_GET, 'q');

		$field = filter_input(INPUT_GET, 'field', FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW & FILTER_FLAG_STRIP_HIGH);

		if ($field) {
			$query .= '%';
			$sql_where = '`' . $field . '` LIKE :query';
		} else {
			$sql_where = "MATCH (p.`name`, p.`pdesc`, p.`profile_url`, p.`locality`, p.`region`, p.`country`, p.`tags` )
AGAINST (:query IN BOOLEAN MODE)";
		}

		$values = ['query' => $query];

		$account_type = $args['account_type'] ?? 'All';
		if ($account_type != 'All') {
			$sql_where .= '
AND `account_type` = :account_type';
			$values['account_type'] = $account_type;
		}

		$profiles = $this->profileModel->getListForDisplay($pager->getItemsPerPage(), $pager->getStart(), $sql_where, $values);

		$count = $this->profileModel->getCountForDisplay($sql_where, $values);

		$vars = [
			'query' => $originalQuery,
			'page' => $pager->getPage(),
			'itemsperpage' => $pager->getItemsPerPage(),
			'count' => $count,
			'profiles' => $profiles
		];

		// Render index view
		return $response->withJson($vars);
	}
}
