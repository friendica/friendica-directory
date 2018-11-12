<?php

namespace Friendica\Directory\Controllers\Web;

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
	 * @var \Friendica\Directory\Views\PhpRenderer
	 */
	private $renderer;
	/**
	 * @var \Friendica\Directory\Views\Widget\AccountTypeTabs
	 */
	private $accountTypeTabs;
	/**
	 * @var \Friendica\Directory\Content\L10n
	 */
	private $l10n;

	public function __construct(
		\Atlas\Pdo\Connection $atlas,
		\Friendica\Directory\Models\Profile $profileModel,
		\Friendica\Directory\Views\Widget\AccountTypeTabs $accountTypeTabs,
		\Friendica\Directory\Views\PhpRenderer $renderer,
		\Friendica\Directory\Content\L10n $l10n
	)
	{
		$this->atlas = $atlas;
		$this->profileModel = $profileModel;
		$this->accountTypeTabs = $accountTypeTabs;
		$this->renderer = $renderer;
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

		$account_type = $args['account_type'] ?? '';
		if ($account_type) {
			$sql_where .= '
AND `account_type` = :account_type';
			$values['account_type'] = $account_type;
		}

		$profiles = $this->profileModel->getListForDisplay($pager->getItemsPerPage(), $pager->getStart(), $sql_where, $values);

		$count = $this->profileModel->getCountForDisplay($sql_where, $values);

		$vars = [
			'query' => $originalQuery,
			'count' => $count,
			'accountTypeTabs' => $this->accountTypeTabs->render('search', $account_type, ['q' => $originalQuery]),
			'profiles' => $profiles,
			'pager_full' => $pager->renderFull($count),
			'pager_minimal' => $pager->renderMinimal($count),
		];

		$content = $this->renderer->fetch('search.phtml', $vars);

		// Render index view
		return $this->renderer->render($response, 'layout.phtml', ['baseUrl' => $request->getUri()->getBaseUrl(), 'content' => $content, 'noNavSearch' => true]);
	}
}
