<?php

namespace Friendica\Directory\Routes\Http;

/**
 * @author Hypolite Petovan <mrpetovan@gmail.com>
 */
class Search extends BaseRoute
{
	public function __invoke(\Slim\Http\Request $request, \Slim\Http\Response $response, array $args): \Slim\Http\Response
	{
		if ($request->getAttribute('negotiation')->getMediaType() == 'application/json') {
			$controller = new \Friendica\Directory\Controllers\Api\Search(
				$this->container->atlas,
				$this->container->get('\Friendica\Directory\Models\Profile'),
				$this->container->l10n
			);
		} else {
			$controller = new \Friendica\Directory\Controllers\Web\Search(
				$this->container->atlas,
				$this->container->get('\Friendica\Directory\Models\Profile'),
				$this->container->get('\Friendica\Directory\Views\Widget\AccountTypeTabs'),
				$this->container->renderer,
				$this->container->l10n
			);
		}

		return $controller->render($request, $response, $args);
	}
}
