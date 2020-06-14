<?php
// Application middleware

use Boronczyk\LocalizationMiddleware;

$app->add(new \Gofabian\Negotiation\NegotiationMiddleware([
	'accept' => ['text/html', 'application/json']
]));


$middleware = new LocalizationMiddleware(
	$container->get('settings')['i18n']['locales'],
	$container->get('settings')['i18n']['default']
);

$middleware->setLocaleCallback(function (string $locale) use ($container) {
	setlocale(LC_ALL, $locale);
	setlocale(LC_ALL, $locale . '.utf8');
	setlocale(LC_ALL, $locale . '.UTF8');
	setlocale(LC_ALL, $locale . '.utf-8');
	setlocale(LC_ALL, $locale . '.UTF-8');

	$langPath = $container->get('settings')['i18n']['path'];

	$translator = $container->get('l10n');
	if (is_a($translator, 'Gettext\GettextTranslator')) {
		// One of them will end up working, right?
		$translator->setLanguage($locale);
		$translator->setLanguage($locale . '.utf8');
		$translator->setLanguage($locale . '.UTF8');
		$translator->setLanguage($locale . '.utf-8');
		$translator->setLanguage($locale . '.UTF-8');

		$translator->loadDomain('strings', $langPath);
	} else {
		$lang = substr($locale, 0, 2);

		/** @var $translator \Gettext\Translator */
		if (file_exists($langPath . '/' . $locale . '/LC_MESSAGES/strings.mo')) {
			$translator->loadTranslations(Gettext\Translations::fromMoFile($langPath . '/' . $locale . '/LC_MESSAGES/strings.mo'));
		} elseif (file_exists($langPath . '/' . $locale . '/LC_MESSAGES/strings.po')) {
			$translator->loadTranslations(Gettext\Translations::fromPoFile($langPath . '/' . $locale . '/LC_MESSAGES/strings.po'));
		} elseif (file_exists($langPath . '/' . $lang . '/LC_MESSAGES/strings.mo')) {
			// Defaulting to language superset
			$translator->loadTranslations(Gettext\Translations::fromMoFile($langPath . '/' . $lang . '/LC_MESSAGES/strings.mo'));
		} elseif (file_exists($langPath . '/' . $lang . '/LC_MESSAGES/strings.po')) {
			// Defaulting to language superset
			$translator->loadTranslations(Gettext\Translations::fromPoFile($langPath . '/' . $lang . '/LC_MESSAGES/strings.po'));
		}
	}
});
$middleware->setUriParamName('lang');

$app->add($middleware);

$app->add(new \Friendica\Directory\Middleware\ZrlMiddleware($container->get('renderer')));
