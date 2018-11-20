<?php

namespace Friendica\Directory\Controllers\Web;


abstract class BaseController
{
	abstract function render(\Slim\Http\Request $request, \Slim\Http\Response $response, array $args): array;
}