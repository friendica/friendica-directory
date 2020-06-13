<?php

namespace Friendica\Directory\Controllers\Web;

use Friendica\Directory\Content\Pager;
use Friendica\Directory\Views\Widget\PopularCountries;
use Friendica\Directory\Views\Widget\PopularLanguages;
use Friendica\Directory\Views\Widget\PopularTags;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * @author Hypolite Petovan <hypolite@mrpetovan.com>
 */
class Directory extends BaseController
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
	 * @var \Friendica\Directory\Views\Widget\AccountTypeTabs
	 */
	private $accountTypeTabs;
	/**
	 * @var \Friendica\Directory\Views\PhpRenderer
	 */
	private $renderer;
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

	public function render(Request $request, Response $response, array $args): array
	{
		$popularTags = new PopularTags($this->atlas, $this->renderer);
		$popularCountries = new PopularCountries($this->atlas, $this->renderer);
		$popularLanguages = new PopularLanguages($this->atlas, $this->renderer);

		$pager = new Pager($this->l10n, $request, 20);

		$condition = '';
		$values = [];
		if (!empty($args['account_type'])) {
			$condition = '`account_type` = :account_type';
			$values = ['account_type' => $args['account_type']];
		}

		$profiles = $this->profileModel->getListForDisplay($pager->getItemsPerPage(), $pager->getStart(), $condition, $values);

		$count = $this->profileModel->getCountForDisplay($condition, $values);

		$vars = [
			'title' => $this->l10n->gettext('People'),
			'profiles' => $profiles,
			'pager_full' => $pager->renderFull($count),
			'pager_minimal' => $pager->renderMinimal($count),
			'accountTypeTabs' => $this->accountTypeTabs->render('directory', $args['account_type'] ?? ''),
			'popularTags' => $popularTags->render(),
			'popularCountries' => $popularCountries->render(),
			'popularLanguages' => $popularLanguages->render(),
		];

		$content = $this->renderer->fetch('directory.phtml', $vars);

		// Render index view
		return ['content' => $content];
	}
}
