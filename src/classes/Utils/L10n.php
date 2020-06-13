<?php

namespace Friendica\Directory\Utils;

use Gettext\Languages\Language;
use Gettext\Translator;

/**
 * @author Hypolite Petovan <hypolite@mrpetovan.com>
 */
class L10n
{
	const DECIMAL = 1;
	const PERCENT = 2;

	public static $languages = [
		'af' => 'Afrikaans',
		'ak' => 'Akan',
		'am' => 'አማርኛ',
		'ar' => 'العربية',
		'as' => 'অসমীয়া',
		'az' => 'Azərbaycan',
		'be' => 'Беларуская',
		'bg' => 'Български',
		'bm' => 'Bamanakan',
		'bn' => 'বাংলা',
		'bo' => 'བོད་སྐད་',
		'br' => 'Brezhoneg',
		'bs' => 'Bosanski',
		'ca' => 'Català',
		'cs' => 'Čeština',
		'cy' => 'Cymraeg',
		'da' => 'Dansk',
		'de' => 'Deutsch',
		'de_AT' => 'Österreichisches Deutsch',
		'de_CH' => 'Schweizer Hochdeutsch',
		'dz' => 'རྫོང་ཁ',
		'ee' => 'Eʋegbe',
		'el' => 'Ελληνικά',
		'en' => 'English',
		'en_AU' => 'Australian English',
		'en_CA' => 'Canadian English',
		'en_GB' => 'British English',
		'en_US' => 'American English',
		'eo' => 'Esperanto',
		'es' => 'Español',
		'es_ES' => 'Español De España',
		'es_MX' => 'Español De México',
		'et' => 'Eesti',
		'eu' => 'Euskara',
		'fa' => 'فارسی',
		'ff' => 'Pulaar',
		'fi' => 'Suomi',
		'fo' => 'Føroyskt',
		'fr' => 'Français',
		'fr_CA' => 'Français Canadien',
		'fr_CH' => 'Français Suisse',
		'fy' => 'West-Frysk',
		'ga' => 'Gaeilge',
		'gd' => 'Gàidhlig',
		'gl' => 'Galego',
		'gu' => 'ગુજરાતી',
		'gv' => 'Gaelg',
		'ha' => 'Hausa',
		'he' => 'עברית',
		'hi' => 'हिन्दी',
		'hr' => 'Hrvatski',
		'hu' => 'Magyar',
		'hy' => 'Հայերեն',
		'id' => 'Bahasa Indonesia',
		'ig' => 'Igbo',
		'ii' => 'ꆈꌠꉙ',
		'is' => 'Íslenska',
		'it' => 'Italiano',
		'ja' => '日本語',
		'ka' => 'ქართული',
		'ki' => 'Gikuyu',
		'kk' => 'Қазақ Тілі',
		'kl' => 'Kalaallisut',
		'km' => 'ខ្មែរ',
		'kn' => 'ಕನ್ನಡ',
		'ko' => '한국어',
		'ks' => 'کٲشُر',
		'kw' => 'Kernewek',
		'ky' => 'Кыргызча',
		'la' => 'Lingua Latina',
		'lb' => 'Lëtzebuergesch',
		'lg' => 'Luganda',
		'ln' => 'Lingála',
		'lo' => 'ລາວ',
		'lt' => 'Lietuvių',
		'lu' => 'Tshiluba',
		'lv' => 'Latviešu',
		'mg' => 'Malagasy',
		'mk' => 'Македонски',
		'ml' => 'മലയാളം',
		'mn' => 'Монгол',
		'mr' => 'मराठी',
		'ms' => 'Bahasa Melayu',
		'mt' => 'Malti',
		'my' => 'ဗမာ',
		'nb' => 'Norsk Bokmål',
		'nd' => 'Isindebele',
		'ne' => 'नेपाली',
		'nl' => 'Nederlands',
		'nl_BE' => 'Vlaams',
		'nn' => 'Nynorsk',
		'no' => 'Norsk',
		'om' => 'Oromoo',
		'or' => 'ଓଡ଼ିଆ',
		'os' => 'Ирон',
		'pa' => 'ਪੰਜਾਬੀ',
		'pl' => 'Polski',
		'ps' => 'پښتو',
		'pt' => 'Português',
		'pt_BR' => 'Português Do Brasil',
		'pt_PT' => 'Português Europeu',
		'qu' => 'Runasimi',
		'rm' => 'Rumantsch',
		'rn' => 'Ikirundi',
		'ro' => 'Română',
		'ro_MD' => 'Moldovenească',
		'ru' => 'Русский',
		'rw' => 'Kinyarwanda',
		'se' => 'Davvisámegiella',
		'sg' => 'Sängö',
		'sh' => 'Srpskohrvatski',
		'si' => 'සිංහල',
		'sk' => 'Slovenčina',
		'sl' => 'Slovenščina',
		'sn' => 'Chishona',
		'so' => 'Soomaali',
		'sq' => 'Shqip',
		'sr' => 'Српски',
		'sv' => 'Svenska',
		'sw' => 'Kiswahili',
		'ta' => 'தமிழ்',
		'te' => 'తెలుగు',
		'th' => 'ไทย',
		'ti' => 'ትግርኛ',
		'tl' => 'Tagalog',
		'to' => 'Lea Fakatonga',
		'tr' => 'Türkçe',
		'ug' => 'ئۇيغۇرچە',
		'uk' => 'Українська',
		'ur' => 'اردو',
		'uz' => 'Oʻzbekcha',
		'vi' => 'Tiếng Việt',
		'yi' => 'ייִדיש',
		'yo' => 'Èdè Yorùbá',
		'zh' => '中文',
		'zh_Hans' => '简体中文',
		'zh_Hant' => '繁體中文',
		'zu' => 'Isizulu',
	];

	public static function localeToLanguageString($locale)
	{
		$lang = substr($locale, 0, 2);

		$foundLocale = false;
		$foundLang = false;
		foreach(self::$languages as $key => $language) {
			if (strtolower($key) == strtolower($lang)) {
				$foundLang = $language;
			}
			if (strtolower($key) == strtolower(str_replace('-', '_', $locale))) {
				$foundLocale = true;
				break;
			}
		}

		return $foundLocale ? $language : $foundLang ?: $locale;
	}

	/**
	 * @param float|int $number
	 * @param int       $style
	 * @return string
	 */
	public static function formatNumber($number, $style = self::DECIMAL)
	{
		$locale = localeconv();

		switch($style) {
			case self::PERCENT:
				$number *= 100;

				if (\intval($number) == $number) {
					$decimals = 0;
				} else {
					$decimals = 2;
				}

				$return = number_format($number, $decimals,
					$locale['decimal_point'],
					$locale['thousands_sep']) . '%';
				break;
			case self::DECIMAL:
			default:
				if (\intval($number) == $number) {
					$decimals = 0;
				} else {
					$decimals = 2;
				}

				$return = number_format($number, $decimals,
					$locale['decimal_point'],
					$locale['thousands_sep']);
				break;
		}

		return $return;
	}
}
