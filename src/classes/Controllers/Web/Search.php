<?php

namespace Friendica\Directory\Controllers\Web;

use \Friendica\Directory\Content\Pager;

/**
 * @author Hypolite Petovan <mrpetovan@gmail.com>
 */
class Search extends BaseController
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
	 * @var \Gettext\TranslatorInterface
	 */
	private $l10n;

	public function __construct(
		\Atlas\Pdo\Connection $atlas,
		\Friendica\Directory\Models\Profile $profileModel,
		\Friendica\Directory\Views\Widget\AccountTypeTabs $accountTypeTabs,
		\Friendica\Directory\Views\PhpRenderer $renderer,
		\Gettext\TranslatorInterface $l10n
	)
	{
		$this->atlas = $atlas;
		$this->profileModel = $profileModel;
		$this->accountTypeTabs = $accountTypeTabs;
		$this->renderer = $renderer;
		$this->l10n = $l10n;
	}

	public function render(\Slim\Http\Request $request, \Slim\Http\Response $response, array $args): array
	{
		$pager = new Pager($this->l10n, $request, 20);

		$originalQuery = $query = filter_input(INPUT_GET, 'q');

		$field = filter_input(INPUT_GET, 'field', FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW & FILTER_FLAG_STRIP_HIGH);

		$fieldName = '';

		if ($field) {
			$query .= '%';
			$sql_where = 'p.`' . $field . '` LIKE :query';

			switch($field) {
				case 'language': $fieldName = $this->l10n->pgettext('field', 'Language'); break;
				case 'locality': $fieldName = $this->l10n->pgettext('field', 'Locality'); break;
				case 'region'  : $fieldName = $this->l10n->pgettext('field', 'Region')  ; break;
				case 'country' : $fieldName = $this->l10n->pgettext('field', 'Country') ; break;
				default: $fieldName = ucfirst($field);
			}

		} else {
			$sql_where = "MATCH (p.`name`, p.`pdesc`, p.`profile_url`, p.`locality`, p.`region`, p.`country`, p.`tags` )
AGAINST (:query IN BOOLEAN MODE)";
		}

		$values = ['query' => $query];


		$account_type = $args['account_type'] ?? '';

		$accountTypeTabs = $this->accountTypeTabs->render('search', $account_type, $sql_where, $values, ['q' => $originalQuery, 'field' => $field]);

		if ($account_type) {
			$sql_where .= '
AND `account_type` = :account_type';
			$values['account_type'] = $account_type;
		}

		$profiles = $this->profileModel->getListForDisplay($pager->getItemsPerPage(), $pager->getStart(), $sql_where, $values);

		$count = $this->profileModel->getCountForDisplay($sql_where, $values);

		$vars = [
			'query'      => $originalQuery,
			'field'      => $field,
			'fieldName'  => $fieldName,
			'count'      => $count,
			'accountTypeTabs' => $accountTypeTabs,
			'profiles'   => $profiles,
			'pager_full' => $pager->renderFull($count),
			'pager_minimal' => $pager->renderMinimal($count),
		];

		$content = $this->renderer->fetch('search.phtml', $vars);

		// Render index view
		return ['content' => $content, 'noNavSearch' => true];
	}
}
