# Friendica Global Directory

This standalone software is meant to provide a global public directory of [Friendica ](https://github.com/friendica/friendica) profiles across nodes.

This is an opt-in directory, meaning that each node can choose not to submit its profiles to global directories, and each user can individually choose not to be submitted.

## Requirements

- PHP >= 7.1 with:
    - Curl
    - GD
    - JSON
- Command-line access
- A web server with URL rewriting (Apache)
- A database server (MariaDB)
- A background task scheduler (Crontab)
- About 60 MB for a Git install
- About 100 MB for a full database

## Installation

Please refer to the provided [installation instructions](INSTALL.md).

## Update from a previous version

Please refer to the provided [update instructions](UPDATE.md).

## Custom pages

If you need to add custom HTML pages as required by law to publish any website processing data in some countries, simply add your HTML files in the `config/pages` folder, they will be automatically linked from the footer.

Tips:
- The expected extension is `.html`.
- Underscores in the page file name are replaced by spaces in the page link label.
- Accents aren't supported.

## See also

- [Project Concepts](docs/Concepts.md)
- [Directory Protocol](docs/Protocol.md)
- [Translation](docs/Translation.md)

## Special thanks

- [Beanow](https://github.com/Beanow) for his efforts to spearhead the previous version of the Friendica Directory software.
- [Scott Arciszewski](https://github.com/paragonie-scott) for his inspiration to use Slim and his invaluable Slim app example.
- [Saša Stamenković](https://github.com/umpirsky) for his useful list packages like [umpirsky/language-list](https://github.com/umpirsky/language-list).