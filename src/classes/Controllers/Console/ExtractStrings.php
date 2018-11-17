<?php

namespace Friendica\Directory\Controllers\Console;

use Friendica\Directory\Utils\L10n;
use Gettext\Merge;
use Gettext\Translations;

/**
 * @author Hypolite Petovan <mrpetovan@gmail.com>
 */
class ExtractStrings extends \Asika\SimpleConsole\Console
{
	/**
	 * @var array
	 */
	protected $locales;

	protected $helpOptions = ['h', 'help', '?'];

	public function __construct(
		array $locales,
		?array $argv = null
	)
	{
		$this->locales = $locales;

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
    --all        Generate po files for all available languages
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
			$langs = $this->locales;
		} else {
			$lang = $this->getArgument(0);
			if (!$lang) {
				throw new \RuntimeException('Missing language argument and --all isn\'t provided');
			}
			$langs = [$lang];
		}

		$outputDir = __DIR__ . '/../../../lang';

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

			$stringsPoFile = $outputDir . '/' . $locale . '/LC_MESSAGES/strings.po';
			if (is_file($stringsPoFile)) {
				if (!$this->getOption('force')) {
					$this->out('Loading existing ' . $locale . ' translations');
					$existingTranslations->addFromPoFile($stringsPoFile);
				}
			} else {
				mkdir(dirname($stringsPoFile), true);
			}

//			$existingPoFile = $outputDir . '/' . $locale . '/LC_MESSAGES/existing.po';
//			$existingPoString = $existingTranslations->toPoString();
//			$existingPoString = str_replace(realpath(__DIR__ . '/../../../../') . DIRECTORY_SEPARATOR, '', $existingPoString);
//			$this->out('Writing ' . realpath($existingPoFile));
//			file_put_contents($existingPoFile, $existingPoString);

//			$updatedPoFile = $outputDir . '/' . $locale . '/LC_MESSAGES/updated.po';
//			$updatedPoString = $updatedTranslations->toPoString();
//			$updatedPoString = str_replace(realpath(__DIR__ . '/../../../../') . DIRECTORY_SEPARATOR, '', $updatedPoString);
//			$this->out('Writing ' . realpath($updatedPoFile));
//			file_put_contents($updatedPoFile, $updatedPoString);

			$updatedTranslations->setLanguage($locale);

			if ($this->getOption('force')) {
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

