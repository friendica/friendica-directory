<?php

namespace Friendica\Directory\Views;

/**
 * Zend-Escaper wrapper for Slim PHP Renderer
 *
 * @author Hypolite Petovan <mrpetovan@gmail.com>
 */
class PhpRenderer extends \Slim\Views\PhpRenderer
{
	/**
	 * @var \Zend\Escaper\Escaper
	 */
	private $escaper;
	/**
	 * @var \Friendica\Directory\Content\L10n
	 */
	private $l10n;

	public function __construct(
		\Zend\Escaper\Escaper $escaper,
		\Friendica\Directory\Content\L10n $l10n,
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

	public function escapeHtml(string $value): string
	{
		return $this->escaper->escapeHtml($value);
	}

	public function escapeCss(string $value): string
	{
		return $this->escaper->escapeCss($value);
	}

	public function escapeJs(string $value): string
	{
		return $this->escaper->escapeJs($value);
	}

	public function escapeHtmlAttr(string $value): string
	{
		return $this->escaper->escapeHtmlAttr($value);
	}

	public function escapeUrl(string $value): string
	{
		return $this->escaper->escapeUrl($value);
	}
}
