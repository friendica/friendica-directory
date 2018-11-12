<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes

/**
 * @var $app \Slim\App
 */

$app->get('/servers', \Friendica\Directory\Routes\Http\Servers::class);

$app->get('/search[/{account_type}]', \Friendica\Directory\Routes\Http\Search::class)->setName('search');

$app->get('/submit', \Friendica\Directory\Routes\Http\Submit::class);

$app->get('/photo/{profile_id:[0-9]+}.jpg', \Friendica\Directory\Routes\Http\Photo::class)->setName('photo');


$app->get('/tag/{term}', function (Request $request, Response $response, $args) {

	$pager = new \Friendica\Directory\Content\Pager($this->l10n, $request, 20);

	$term = $args['term'];

	$sql_where = 'FROM `profile` p
JOIN `tag` t ON p.`nurl` = t.`nurl`
WHERE `term` = :term
AND NOT `hidden`
AND `available`';

	$stmt = 'SELECT *
' . $sql_where . '
ORDER BY `filled_fields` DESC, `last_activity` DESC, `updated` DESC LIMIT :start, :limit';
	$profiles = $this->atlas->fetchAll($stmt, [
		'term' => $term,
		'start' => [$pager->getStart(), PDO::PARAM_INT],
		'limit' => [$pager->getItemsPerPage(), PDO::PARAM_INT]
	]);

	$stmt = 'SELECT COUNT(*) AS `total`
' . $sql_where;
	$count = $this->atlas->fetchValue($stmt, ['term' => $term]);

	$vars = [
		'term' => $term,
		'count' => $count,
		'profiles' => $profiles,
		'pager' => $pager->renderFull($count),
	];

	$content = $this->renderer->fetch('tag.phtml', $vars);

	// Render index view
	return $this->renderer->render($response, 'layout.phtml', ['baseUrl' => $request->getUri()->getBaseUrl(), 'content' => $content]);
});

$app->get('/sync/pull/all', \Friendica\Directory\Routes\Http\SyncPull::class);
$app->get('/sync/pull/since/{since}', \Friendica\Directory\Routes\Http\SyncPull::class);

$app->get('/VERSION', function (Request $request, Response $response) {
	$response->getBody()->write(file_get_contents(__DIR__ . '/../VERSION'));

	return $response;
});

$app->get('/[{account_type}]', \Friendica\Directory\Routes\Http\Directory::class)->setName('directory');
