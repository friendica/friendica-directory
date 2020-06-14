<?php

namespace Friendica\Directory\Controllers\Web;

/**
 * @author Hypolite Petovan <hypolite@mrpetovan.com>
 */
class Page extends BaseController
{
	/**
	 * @var string
	 */
	private $pageFile;

	public function __construct(
		string $pageFile
	)
	{
		$this->pageFile = $pageFile;
	}

	public function render(\Slim\Http\Request $request, \Slim\Http\Response $response, array $args): array
	{
		$content = file_get_contents($this->pageFile);

		return ['content' => $content];
	}
}
