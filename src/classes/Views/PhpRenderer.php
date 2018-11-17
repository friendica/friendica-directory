<?php

namespace Friendica\Directory\Views;

/**
 * Zend-Escaper wrapper for Slim PHP Renderer
 *
 * @author Hypolite Petovan <mrpetovan@gmail.com>
 *
 * @method string escapeHtml(string $value)
 * @method string escapeHtmlAttr(string $value)
 * @method string escapeCss(string $value)
 * @method string escapeJs(string $value)
 * @method string escapeUrl(string $value)
 * @method string noop(string $original)
 * @method string gettext(string $original)
 * @method string ngettext(string $original, string $plural, string $value)
 * @method string dngettext(string $domain, string $original, string $plural, string $value)
 * @method string npgettext(string $context, string $original, string $plural, string $value)
 * @method string pgettext(string $context, string $original)
 * @method string dgettext(string $domain, string $original)
 * @method string dpgettext(string $domain, string $context, string $original)
 * @method string dnpgettext(string $domain, string $context, string $original, string $plural, string $value)
 */
class PhpRenderer extends \Slim\Views\PhpRenderer
{
	/**
	 * @var \Zend\Escaper\Escaper
	 */
	private $escaper;
	/**
	 * @var \Gettext\TranslatorInterface
	 */
	private $l10n;

	public function __construct(
		\Zend\Escaper\Escaper $escaper,
		\Gettext\TranslatorInterface $l10n,
		string $templatePath = "",
		array $attributes = array()
	)
	{
		parent::__construct($templatePath, $attributes);

		$this->escaper = $escaper;
		$this->l10n = $l10n;
	}

	public function e(string $value): string
	{
		return $this->escapeHtml($value);
	}

	public function __call($name, $arguments)
	{
		if (method_exists($this->escaper, $name)) {
			return $this->escaper->$name(...$arguments);
		} elseif (method_exists($this->l10n, $name)) {
			return $this->l10n->$name(...$arguments);
		} else {
			throw new \Exception('Unknown PhpRendere magic method: ' . $name);
		}
	}

	/**
	 * Echoes the translation of a string.
	 *
	 * Loose copy of Gettext/gettext global __() function
	 *
	 * Usages:
	 * - $this->__('Label')
	 * - $this->__('Label %s', $value)
	 *
	 * @param $original
	 * @param array  $args
	 *
	 * @return string
	 */
	public function __($original, ...$args)
	{
		$text = $this->l10n->gettext($original);

		if (!count($args)) {
			return $text;
		}

		return is_array($args[0]) ? strtr($text, $args[0]) : vsprintf($text, $args);
	}

	/**
	 * Returns the translation of a string in a specific context.
	 *
	 * @param string $context
	 * @param string $original
	 *
	 * @param array  $args
	 * @return string
	 */
	function p__($context, $original, ...$args)
	{
		$text = $this->l10n->pgettext($context, $original);

		if (!count($args)) {
			return $text;
		}

		return is_array($args[0]) ? strtr($text, $args[0]) : vsprintf($text, $args);
	}

	/**
	 * Returns the translation of a string in a specific domain.
	 *
	 * @param string $domain
	 * @param string $original
	 *
	 * @param array  $args
	 * @return string
	 */
	function d__($domain, $original, ...$args)
	{
		$text = $this->l10n->dgettext($domain, $original);

		if (!count($args)) {
			return $text;
		}

		return is_array($args[0]) ? strtr($text, $args[0]) : vsprintf($text, $args);
	}

	/**
	 * Echoes the singular/plural translation of a string.
	 *
	 * Loose copy of Gettext/gettext global n__() function
	 *
	 * Usages:
	 * - $this->n__('Label', 'Labels', 3)
	 * - $this->n__('%d Label for %s', '%d Labels for %s', 3, $value)
	 *
	 * @param string $original
	 * @param string $plural
	 * @param string $count
	 * @param array  $args
	 *
	 * @return string
	 */
	function n__($original, $plural, $count, ...$args)
	{
		$text = $this->l10n->ngettext($original, $plural, $count);

		array_unshift($args, $count);

		return is_array($args[1]) ? strtr($text, $args[1]) : vsprintf($text, $args);
	}

	/**
	 * Returns the singular/plural translation of a string in a specific context
	 *
	 * Usages:
	 * - $this->n__('search', 'Label', 'Labels', 3)
	 * - $this->n__('search', '%d Label for %s', '%d Labels for %s', 3, $value)
	 *
	 * @param string $context
	 * @param string $original
	 * @param string $plural
	 * @param int    $count
	 * @param array  ...$args
	 * @return string
	 */
	function np__(string $context, string $original, string $plural, int $count, array ...$args)
	{
		$text = $this->l10n->npgettext($context, $original, $plural, $count);

		array_unshift($args, $count);

		return !empty($args[1]) && is_array($args[1]) ? strtr($text, $args[1]) : vsprintf($text, $args);
	}


}
