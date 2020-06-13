<?php

namespace Friendica\Directory\Controllers\Console;

use Gettext\Merge;
use Gettext\Translations;

/**
 * @author Hypolite Petovan <hypolite@mrpetovan.com>
 */
class ExtractStrings extends \Asika\SimpleConsole\Console
{
	/**
	 * @var string
	 */
	protected $translationPath;

	protected $helpOptions = ['h', 'help', '?'];

	public function __construct(
		string $translationPath,
		?array $argv = null
	)
	{
		$this->translationPath = $translationPath;

		parent::__construct($argv);
	}

	protected function getHelp()
	{
		$help = <<<HELP
console extract-strings - Extract translation strings
Usage
	bin/console extract-strings [language] [--all] [--force] [-h|--help|-?] [-v]

Description
	Extract translation strings

Options
    --all        Update PO files for all existing languages
    --force      Generate po file from scratch, discarding existing translations
    -h|--help|-? Show help information
    -v           Show more debug information.
HELP;
		return $help;
	}

	protected function doExecute()
	{
		if (count($this->args) > 1) {
			throw new \Asika\SimpleConsole\CommandArgsException('Too many arguments');
		}

		if ($this->getOption('all')) {
			$langs = array_map('basename', glob(realpath($this->translationPath) . '/*', GLOB_ONLYDIR));
		} else {
			$lang = $this->getArgument(0);
			if (!$lang) {
				throw new \RuntimeException('Missing language argument and --all isn\'t provided');
			}
			$langs = [$lang];
		}

		$dir_iterator = new \RecursiveDirectoryIterator(realpath(__DIR__ . '/../../../'), \FilesystemIterator::SKIP_DOTS);
		$iterator = new \RecursiveIteratorIterator($dir_iterator, \RecursiveIteratorIterator::SELF_FIRST);

		$updatedTranslations = new Translations();
		foreach ($iterator as $file) {
			/**
			 * @var \SplFileInfo $file
			 */
			$extension = $file->getExtension();

			if ($extension == 'php' || $extension == 'phtml') {
				$translations = Translations::fromPhpCodeFile($file->getPathname());
				if (count($translations)) {
					$updatedTranslations->mergeWith($translations);
				}
			}
		}

		$this->out('Compiled up-to-date translations');

		foreach ($langs as $locale) {
			$existingTranslations = new Translations();

			$stringsPoFile = $this->translationPath . '/' . $locale . '/LC_MESSAGES/strings.po';
			if (is_file($stringsPoFile)) {
				if (!$this->getOption('force')) {
					$this->out('Loading existing ' . $locale . ' translations');
					$existingTranslations->addFromPoFile($stringsPoFile);
				}
			} else {
				$this->out('Creating directory ' . dirname($stringsPoFile));
				mkdir(dirname($stringsPoFile), 0755, true);
			}

			$updatedTranslations->setLanguage($locale);

			if ($this->getOption('force') || !is_file($stringsPoFile)) {
				$existingTranslations = $updatedTranslations;
			} else {
				$this->out('Merging with existing translations');

				$existingTranslations->mergeWith($updatedTranslations, Merge::ADD | Merge::REMOVE | Merge::REFERENCES_THEIRS | Merge::HEADERS_ADD);
			}

			$poString = $existingTranslations->toPoString();

			// Strip absolute path to files
			$poString = str_replace(realpath(__DIR__ . '/../../../../') . DIRECTORY_SEPARATOR, '', $poString);

			$this->out('Writing ' . realpath($stringsPoFile));
			file_put_contents($stringsPoFile, $poString);
		}

		return 0;
	}
}

