# Friendica Directory Project Concepts

## Dependency Injection

Friendica Directory uses a Service Container that holds all the services that can potentially be needed.
It is defined in `src/dependencies.php`.

However, this Container isn't globally available to classes. Instead, we use factory classes and closures to construct each classes with just the required services.

This way, we can test classes in isolation without having to care for a full-featured Container.

## Entry points, Controllers and Routes

There are 3 entry points to the Friendica Directory:
- The web server with the `Slim\App` class, taking HTTP requests and outputting HTML pages.
- The command-line console with the `Friendica\Directory\Controllers\Console` class taking command-line parameters and outputting plain text.
- The background task with the `Friendica\Directory\Controllers\Cron.php` class with no input and no output.

Next up, there are a variety of Controllers in the subfolders of `src/classes/Controllers` depending on the intended use:
- `Api/` for web controllers outputting JSON.
- `Console/` for the various sub console commands.
- `Web/` for the displayed HTML pages.

To match the latter with the former, Routes are defined in `src/classes/Routes` based on the entry point.

Multiple routes can reference the same Controller, we use this feature to offer the same sub console for different spelling of the same command.

More importantly, Routes are the above-mentioned factory classes tasked with instantiating the Controllers with the correct services.

## Database migrations

In `src/sql` can be found the base SQL schema as it was at the start of the project, and in `src/migrations/up` the various additional SQL scripts that have been added to it.

Scripts are named after the database version it is upgrading the schema to, and is incremented each time we need to alter the schema.

This allows individual Friendica Directory installs to seamlessly upgrade their schema when pulling the new code, no matter how old the last update was.

To update to the latest schema, simply run `bin/console dbupdate`.

## PHP Templating

Friendica Directory relies on pure PHP templates. The base layout is `src/templates/layout.phtml`, full page templates are located in the same directory and sub-templates are found in the subfolders.

The main challenge with PHP templates is escaping dynamic values to prevent XSS attacks.
This is done using [Zend Escaper](https://framework.zend.com/manual/2.4/en/modules/zend.escaper.introduction.html) and convenience wrapper methods have been added to the PHPRenderer to be able to use it in the templates.

Sample usage:
```php
<script type="text/javascript">
    var foo = <?php echo $this->escapeJs($js)?>;
</script>
<style>
    <?php echo $this->escapeCss($css); ?>
</style>
<div class="<?php echo $this->escapeHtmlAttr($attr)?>">
    <?php echo $this->e($body)?>
    <a href="?url=<?php echo $this->escapeUrl($attr)?>">Link</a>
</div>
```