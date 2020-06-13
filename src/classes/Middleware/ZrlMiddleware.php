<?php

namespace Friendica\Directory\Middleware;

use Friendica\Directory\Views\PhpRenderer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * The ZRL middleware ensures the Renderer ZRL attribute is set if the query string parameter is present
 *
 * @author Hypolite Petovan <hypolite@mrpetovan.com>
 * @package Friendica\Directory\Middleware
 */
class ZrlMiddleware
{
	/**
	 * @var PhpRenderer
	 */
	private $phpRenderer;

	public function __construct(PhpRenderer $phpRenderer)
	{
		$this->phpRenderer = $phpRenderer;
	}

	/**
	 * @param ServerRequestInterface $request  PSR7 request
	 * @param ResponseInterface      $response PSR7 response
	 * @param callable               $next     Next middleware
	 *
	 * @return ResponseInterface
	 */
	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
	{
		$this->phpRenderer->addAttribute('zrl', $request->getQueryParams()['zrl'] ?? null);

		return $next($request, $response);
	}
}
