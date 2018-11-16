# Friendica Directory Localization

## Overview

The Friendica Directory interface is available in multiple languages.

We are using a gettext-like process to generate and parse translations files, which enables translators to use their usual software to translate strings.

The translations files are located in `src/lang/<locale>/LC_MESSAGES/`:
- The compiled MO file can be generated from a translation software and will be loaded by default.
- The PO file is the main translation interface and can be loaded if the MO file doesn't exist.

## Main scenarios

### Add a new locale

Adding a new locale requires to edit `src/settings.php` to add it to the `$settings['i18n']['locales']` array.

The base translation file can then be generated with `bin/console extract-string <locale>`.

### Translate existing strings

PO files can be edited with translation software like [Poedit](https://poedit.net).
Please make sure your software is able to compile MO files to update the existing one.

Both PO and MO files should be committed with Git before being submitted in a GitHub pull request.
Please refer to the [GitHub flow documentation](https://help.github.com/articles/github-flow/) if you're unfamiliar with it.

### Add new translation strings

Once templates/controllers files have been edited with new translation strings, you can run `bin/console extract-strings --all` to update the PO files of all available languages at once.

## Translation functions usage

### In templates

Basic usage:
- `$this->__('Label')` => `Label`
- `$this->__('Label %s', 'test')` => `Label test`

With context, if a base english term can have multiple meanings:  
- `$this->p__('noun', 'Search')` => `Recherche`
- `$this->p__('verb', 'Search')` => `Rechercher`
- `$this->p__('noun', 'Search %s', 'test')` => `Recherche test`
- `$this->p__('verb', 'Search %s', 'test')` => `Rechercher test`

With plurals:
- `$this->p__('Label', 'Labels', 1)` => `Label`
- `$this->p__('Label', 'Labels', 3)` => `Labels`
- `$this->p__('%d Label', '%d Labels', 1)` => `1 Label`
- `$this->p__('%d Label', '%d Labels', 3)` => `3 Labels`
- `$this->p__('%d Label', 'Labels %2%s %3%s', 1, 'test', 'test2')` => `Label test test2`
- `$this->p__('%d Label', 'Labels %2%s %3%s', 3, 'test', 'test2')` => `Labels test test2`

### In classes

You will need to add the `l10n` dependency to your class before you can use it (see [Pager](src/classes/Content/Pager.php)).

- `$this->l10n->gettext('Label')`
- `$this->l10n->pgettext('context', 'Label')`
- `$this->l10n->ngettext('Label', 'Labels', 3)`
- `$this->l10n->npgettext('context', 'Label', 'Labels', 3)`
