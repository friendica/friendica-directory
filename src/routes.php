<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes

/**
 * @var $app \Slim\App
 */

$app->get('/servers', \Friendica\Directory\Routes\Web\Servers::class);

$app->get('/search[/{account_type}]', function (Request $request, Response $response, $args) {
	if ($request->getAttribute('negotiation')->getMediaType() == 'application/json') {
		$route = new \Friendica\Directory\Routes\Http\Search($this);
	} else {
		$route = new \Friendica\Directory\Routes\Web\Search($this);
	}

	return $route($request, $response, $args);
})->setName('search');

$app->get('/stats', \Friendica\Directory\Routes\Web\Statistics::class);

$app->get('/submit', \Friendica\Directory\Routes\Http\Submit::class);

$app->get('/photo/{profile_id:[0-9]+}.jpg', \Friendica\Directory\Routes\Http\Photo::class)->setName('photo');

$app->get('/sync/pull/all', \Friendica\Directory\Routes\Http\SyncPull::class);
$app->get('/sync/pull/since/{since}', \Friendica\Directory\Routes\Http\SyncPull::class);

$app->get('/VERSION', function (Request $request, Response $response) {
	$response->getBody()->write(file_get_contents(__DIR__ . '/../VERSION'));

	return $response;
});

$app->get('/[{account_type}]', \Friendica\Directory\Routes\Web\Directory::class)->setName('directory');
