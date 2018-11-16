<?php

namespace Friendica\Directory\Controllers;

use Monolog\Logger;

/**
 * Description of Console
 *
 * @author Hypolite Petovan <mrpetovan@gmail.com>
 */
class Console extends \Asika\SimpleConsole\Console
{
	/**
	 * @var \Slim\Container
	 */
	protected $container;

	// Disables the default help handling
	protected $helpOptions = [];
	protected $customHelpOptions = ['h', 'help', '?'];

	protected $routes = [
		'directory-add' => \Friendica\Directory\Routes\Console\DirectoryAdd::class,
		'directory-poll' => \Friendica\Directory\Routes\Console\DirectoryPoll::class,
		'profile-add' => \Friendica\Directory\Routes\Console\ProfileAdd::class,
		'profile-hide' => \Friendica\Directory\Routes\Console\ProfileHide::class,
		'profile-poll' => \Friendica\Directory\Routes\Console\ProfilePoll::class,
		'server-hide' => \Friendica\Directory\Routes\Console\ServerHide::class,
		'server-poll' => \Friendica\Directory\Routes\Console\ServerPoll::class,
		'install' => \Friendica\Directory\Routes\Console\Install::class,
		'updatedb' => \Friendica\Directory\Routes\Console\UpdateDb::class,
		'dbupdate' => \Friendica\Directory\Routes\Console\UpdateDb::class,
		'extract-strings' => \Friendica\Directory\Routes\Console\ExtractStrings::class,
	];

	public function __construct(\Slim\Container $container, ?array $argv = null)
	{
		parent::__construct($argv);

		$this->container = $container;
	}

	protected function getHelp()
	{
		$commandList = '';
		foreach ($this->routes as $command => $class) {
			$commandList .= '	' . $command . "\n";
		}

		$help = <<<HELP
Usage: bin/console [--version] [-h|--help|-?] <command> [<args>] [-v]

Commands:
$commandList

Options:
	-h|--help|-? Show help information
	-v           Show more debug information.
HELP;
		return $help;
	}

	protected function doExecute()
	{
		$showHelp = false;
		$subHelp = false;
		$command = null;

		if ($this->getOption('version')) {
			//$this->out('Friendica Console version ' . FRIENDICA_VERSION);

			return 0;
		} elseif ((count($this->options) === 0 || $this->getOption($this->customHelpOptions) === true || $this->getOption($this->customHelpOptions) === 1) && count($this->args) === 0
		) {
			$showHelp = true;
		} elseif (count($this->args) >= 2 && $this->getArgument(0) == 'help') {
			$command = $this->getArgument(1);
			$subHelp = true;
			array_shift($this->args);
			array_shift($this->args);
		} elseif (count($this->args) >= 1) {
			$command = $this->getArgument(0);
			array_shift($this->args);
		}

		if (is_null($command)) {
			$this->out($this->getHelp());
			return 0;
		}

		// Increasing the logger level if -v is provided
		if ($this->getOption('v')) {
			/** @var \Monolog\Logger $logger */
			$handler = $this->container->get('logger')->popHandler();

			$handler->setLevel(\Monolog\Logger::DEBUG);

			$this->container->get('logger')->pushHandler($handler);
		}

		$console = $this->getSubConsole($command);

		if ($subHelp) {
			$console->setOption($this->customHelpOptions, true);
		}

		return $console->execute();
	}

	private function getSubConsole($command): \Asika\SimpleConsole\Console
	{
		$this->container->get('logger')->debug('Command: ' . $command);

		if (!isset($this->routes[$command])) {
			throw new \Asika\SimpleConsole\CommandArgsException('Command ' . $command . ' doesn\'t exist');
		}

		$subargs = $this->args;
		array_unshift($subargs, $this->executable);

		$routeClassName = $this->routes[$command];

		$consoleRoute = new $routeClassName($this->container);

		/** @var \Asika\SimpleConsole\Console $subconsole */
		$subconsole = $consoleRoute($subargs);

		foreach ($this->options as $name => $value) {
			$subconsole->setOption($name, $value);
		}

		return $subconsole;
	}

}
