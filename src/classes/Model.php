<?php

namespace Friendica\Directory;

/**
 * @author Hypolite Petovan <mrpetovan@gmail.com>
 */
class Model
{
	/**
	 *
	 * @var \Atlas\Pdo\Connection
	 */
	protected $atlas;

	public function __construct(\Atlas\Pdo\Connection $atlas)
	{
		$this->atlas = $atlas;
	}
}
