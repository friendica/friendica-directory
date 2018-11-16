<?php

namespace Friendica\Directory\Content;

/**
 * The Pager has two very different output, Minimal and Full, see renderMinimal() and renderFull() for more details.
 *
 * @author Hypolite Petovan <mrpetovan@gmail.com>
 */
class Pager
{
	/**
	 * @var integer
	 */
	private $page = 1;
	/**
	 * @var integer
	 */
	private $itemsPerPage = 50;

	/**
	 * @var string
	 */
	private $baseQueryString = '';

	/**
	 * @var \Gettext\TranslatorInterface
	 */
	private $l10n;

	/**
	 * Instantiates a new Pager with the base parameters.
	 *
	 * Guesses the page number from the GET parameter 'page'.
	 *
	 * @param \Gettext\TranslatorInterface        $l10n
	 * @param \Psr\Http\Message\ServerRequestInterface $request
	 * @param integer                                  $itemsPerPage An optional number of items per page to override the default value
	 */
	public function __construct(\Gettext\TranslatorInterface $l10n, \Psr\Http\Message\ServerRequestInterface $request, int $itemsPerPage = 50)
	{
		$this->l10n = $l10n;
		$this->setQueryString($request);
		$this->setItemsPerPage($itemsPerPage);
		$this->setPage(filter_input(INPUT_GET, 'page', FILTER_SANITIZE_NUMBER_INT));
	}

	/**
	 * Returns the start offset for a LIMIT clause. Starts at 0.
	 *
	 * @return integer
	 */
	public function getStart()
	{
		return max(0, ($this->page * $this->itemsPerPage) - $this->itemsPerPage);
	}

	/**
	 * Returns the number of items per page
	 *
	 * @return integer
	 */
	public function getItemsPerPage()
	{
		return $this->itemsPerPage;
	}

	/**
	 * Returns the current page number
	 *
	 * @return int
	 */
	public function getPage(): int
	{
		return $this->page;
	}

	/**
	 * Returns the base query string.
	 *
	 * Warning: this isn't the same value as passed to the constructor.
	 * See setQueryString() for the inventory of transformations
	 *
	 * @see setBaseQuery()
	 * @return string
	 */
	public function getBaseQueryString(): string
	{
		return $this->baseQueryString;
	}

	/**
	 * Sets the number of items per page, 1 minimum.
	 *
	 * @param integer $itemsPerPage
	 */
	public function setItemsPerPage($itemsPerPage): void
	{
		$this->itemsPerPage = max(1, intval($itemsPerPage));
	}

	/**
	 * Sets the current page number. Starts at 1.
	 *
	 * @param integer $page
	 */
	public function setPage($page): void
	{
		$this->page = max(1, intval($page));
	}

	/**
	 * Sets the base query string from a full query string.
	 *
	 * Strips the 'page' parameter, and remove the 'q=' string for some reason.
	 *
	 * @param \Psr\Http\Message\ServerRequestInterface $request
	 */
	public function setQueryString(\Psr\Http\Message\ServerRequestInterface $request): void
	{
		$queryParams = $request->getQueryParams();
		unset($queryParams['page']);

		$this->baseQueryString = $request->getUri()->getPath() . ($queryParams ? '?' . http_build_query($queryParams) : '');
	}

	/**
	 * Ensures the provided URI has its query string punctuation in order.
	 *
	 * @param string $uri
	 * @return string
	 */
	private function ensureQueryParameter($uri)
	{
		if (strpos($uri, '?') === false && ($pos = strpos($uri, '&')) !== false) {
			$uri = substr($uri, 0, $pos) . '?' . substr($uri, $pos + 1);
		}

		return $uri;
	}

	/**
	 * @brief Minimal pager (newer/older)
	 *
	 * This mode is intended for reverse chronological pages and presents only two links, newer (previous) and older (next).
	 * The itemCount is the number of displayed items. If no items are displayed, the older button is disabled.
	 *
	 * Example usage:
	 *
	 * $pager = new Pager($a->query_string);
	 *
	 * $params = ['order' => ['sort_field' => true], 'limit' => [$pager->getStart(), $pager->getItemsPerPage()]];
	 * $items = DBA::toArray(DBA::select($table, $fields, $condition, $params));
	 *
	 * $html = $pager->renderMinimal(count($items));
	 *
	 * @param integer $itemCount The number of displayed items on the page
	 * @return array of links
	 */
	public function renderMinimal(int $itemCount)
	{
		$displayedItemCount = max(0, $itemCount);

		$data = [
			'class' => 'pager',
			'prev' => [
				'url' => $this->ensureQueryParameter($this->baseQueryString . '&page=' . ($this->getPage() - 1)),
				'text' => $this->l10n->gettext('Previous'),
				'class' => 'previous' . ($this->getPage() == 1 ? ' disabled' : '')
			],
			'next' => [
				'url' => $this->ensureQueryParameter($this->baseQueryString . '&page=' . ($this->getPage() + 1)),
				'text' => $this->l10n->gettext('Next'),
				'class' => 'next' . ($displayedItemCount <= 0 ? ' disabled' : '')
			]
		];

		return $data;
	}

	/**
	 * @brief Full pager (first / prev / 1 / 2 / ... / 14 / 15 / next / last)
	 *
	 * This mode presents page numbers as well as first, previous, next and last links.
	 * The itemCount is the total number of items including those not displayed.
	 *
	 * Example usage:
	 *
	 * $total = DBA::count($table, $condition);
	 *
	 * $pager = new Pager($a->query_string, $total);
	 *
	 * $params = ['limit' => [$pager->getStart(), $pager->getItemsPerPage()]];
	 * $items = DBA::toArray(DBA::select($table, $fields, $condition, $params));
	 *
	 * $html = $pager->renderFull();
	 *
	 * @param integer $itemCount The total number of items including those note displayed on the page
	 * @return array of links
	 */
	public function renderFull($itemCount)
	{
		$totalItemCount = max(0, intval($itemCount));

		$data = [];

		$data['class'] = 'pagination';
		if ($totalItemCount > $this->getItemsPerPage()) {
			$data['first'] = [
				'url' => $this->ensureQueryParameter($this->baseQueryString . '&page=1'),
				'text' => $this->l10n->gettext('First'),
				'class' => $this->getPage() == 1 ? 'disabled' : ''
			];
			$data['prev'] = [
				'url' => $this->ensureQueryParameter($this->baseQueryString . '&page=' . ($this->getPage() - 1)),
				'text' => $this->l10n->gettext('Previous'),
				'class' => $this->getPage() == 1 ? 'disabled' : ''
			];

			$numpages = $totalItemCount / $this->getItemsPerPage();

			$numstart = 1;
			$numstop = $numpages;

			$numpages_limit = 6;

			// Limit the number of displayed page number buttons.
			if ($numpages > $numpages_limit) {
				$numstart = (($this->getPage() > $numpages_limit / 2) ? ($this->getPage() - $numpages_limit / 2) : 1);
				$numstop = (($this->getPage() > ($numpages - $numpages_limit)) ? $numpages : ($numstart + $numpages_limit - 1));
			}

			$pages = [];

			for ($i = $numstart; $i <= $numstop; $i++) {
				if ($i == $this->getPage()) {
					$pages[$i] = [
						'url' => '#',
						'text' => $i,
						'class' => 'current active'
					];
				} else {
					$pages[$i] = [
						'url' => $this->ensureQueryParameter($this->baseQueryString . '&page=' . $i),
						'text' => $i,
						'class' => 'n'
					];
				}
			}

			if (($totalItemCount % $this->getItemsPerPage()) != 0) {
				if ($i == $this->getPage()) {
					$pages[$i] = [
						'url' => '#',
						'text' => $i,
						'class' => 'current active'
					];
				} else {
					$pages[$i] = [
						'url' => $this->ensureQueryParameter($this->baseQueryString . '&page=' . $i),
						'text' => $i,
						'class' => 'n'
					];
				}
			}

			$data['pages'] = $pages;

			$lastpage = (($numpages > intval($numpages)) ? intval($numpages) + 1 : $numpages);

			$data['next'] = [
				'url' => $this->ensureQueryParameter($this->baseQueryString . '&page=' . ($this->getPage() + 1)),
				'text' => $this->l10n->gettext('Next'),
				'class' => $this->getPage() == $lastpage ? 'disabled' : ''
			];
			$data['last'] = [
				'url' => $this->ensureQueryParameter($this->baseQueryString . '&page=' . $lastpage),
				'text' => $this->l10n->gettext('Last'),
				'class' => $this->getPage() == $lastpage ? 'disabled' : ''
			];
		}

		return $data;
	}
}
