<?php

namespace Friendica\Directory\Controllers\Web;

use \Friendica\Directory\Content\Pager;
use PDO;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * @author Hypolite Petovan <mrpetovan@gmail.com>
 */
class Servers extends BaseController
{
	/**
	 * @var \Atlas\Pdo\Connection
	 */
	private $atlas;
	/**
	 * @var \Friendica\Directory\Views\PhpRenderer
	 */
	private $renderer;
	/**
	 * @var \Gettext\TranslatorInterface
	 */
	private $l10n;
	/**
	 * @var \Psr\SimpleCache\CacheInterface
	 */
	private $simplecache;

	public function __construct(
		\Atlas\Pdo\Connection $atlas,
		\Friendica\Directory\Views\PhpRenderer $renderer,
		\Gettext\TranslatorInterface $l10n,
		\Psr\SimpleCache\CacheInterface $simplecache
	)
	{
		$this->atlas = $atlas;
		$this->renderer = $renderer;
		$this->l10n = $l10n;
		$this->simplecache = $simplecache;
	}

	public function render(Request $request, Response $response, array $args): array
	{
		$stable_version = $this->simplecache->get('stable_version');
		if (!$stable_version) {
			$stable_version = trim(file_get_contents('https://git.friendi.ca/friendica/friendica/raw/branch/master/VERSION'));
			$this->simplecache->set('stable_version', $stable_version);
		}

		$dev_version = $this->simplecache->get('dev_version');
		if (!$dev_version) {
			$dev_version = trim(file_get_contents('https://git.friendi.ca/friendica/friendica/raw/branch/develop/VERSION'));
			$this->simplecache->set('dev_version', $dev_version);
		}

		$rc_version = str_replace('-dev', '-rc', $dev_version);

		$pager = new Pager($this->l10n, $request, 20);

		$stmt = 'SELECT *
FROM `server` s
WHERE `reg_policy` != "REGISTER_CLOSED"
AND `available`
AND NOT `hidden`
ORDER BY `health_score` DESC, `ssl_state` DESC, `info` != "" DESC, `last_seen` DESC
LIMIT :start, :limit';
		$servers = $this->atlas->fetchAll($stmt, [
			'start' => [$pager->getStart(), PDO::PARAM_INT],
			'limit' => [$pager->getItemsPerPage(), PDO::PARAM_INT]
		]);

		foreach ($servers as $key => $server) {
			$servers[$key]['user_count'] = $this->atlas->fetchValue(
				'SELECT COUNT(*) FROM `profile` WHERE `available` AND NOT `hidden` AND `server_id` = :server_id',
				['server_id' => [$server['id'], PDO::PARAM_INT]]
			);
		}

		$stmt = 'SELECT COUNT(*)
FROM `server` s
WHERE `reg_policy` != "REGISTER_CLOSED"
AND `available`
AND NOT `hidden`';
		$count = $this->atlas->fetchValue($stmt);

		$vars = [
			'title' => $this->l10n->gettext('Public Servers'),
			'servers' => $servers,
			'pager' => $pager->renderFull($count),
			'stable_version' => $stable_version,
			'rc_version' => $rc_version,
			'dev_version' => $dev_version,
		];

		$content = $this->renderer->fetch('servers.phtml', $vars);

		// Render index view
		return ['content' => $content];
	}
}
