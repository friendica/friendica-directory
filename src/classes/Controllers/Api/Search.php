<?php

namespace Friendica\Directory\Controllers\Api;

use Friendica\Directory\Content\Pager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @author Hypolite Petovan <hypolite@mrpetovan.com>
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
		$limit = min(100, filter_input(INPUT_GET, 'limit', FILTER_SANITIZE_NUMBER_INT) ?: 20);

		$pager = new Pager($this->l10n, $request, $limit);

		$originalQuery = $query = filter_input(INPUT_GET, 'q');

		$field = filter_input(INPUT_GET, 'field', FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW & FILTER_FLAG_STRIP_HIGH);

		if ($field) {
			$query .= '%';
			$sql_where = '`' . $field . '` LIKE :query';
		} else {
			$sql_where = "MATCH (p.`name`, p.`pdesc`, p.`username`, p.`locality`, p.`region`, p.`country`, p.`tags` )
AGAINST (:query IN BOOLEAN MODE)";
			// At sign (@) is a reserved symbol in InnoDB full-text search, it can't be escaped
			$query = str_replace('@', ' ', $query);
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
			'query'        => $originalQuery,
			'field'        => $field,
			'page'         => $pager->getPage(),
			'itemsperpage' => $pager->getItemsPerPage(),
			'count'        => $count,
			'profiles'     => $profiles
		];

		return $response->withJson($vars);
	}
}
