# Friendica Directory Install Instructions

## 1. Get the source code

For a planned install of Friendica Directory in the `/path/to/friendica-directory` folder.

### Using Git and Composer

Git is a popular version control management tool.
[Getting Started with Git](https://git-scm.com/book/en/v2/Getting-Started-Installing-Git).

You'll also need Composer to grab the project dependencies.
Composer is a popular dependency management tool for PHP projects.
[Getting Started with Composer](https://getcomposer.org/doc/00-intro.md).

```
cd /path/to
git clone https://github.com/friendica/friendica-directory friendica-directory
cd friendica-directory
composer install
```

### Using a stable release archive

On the [Friendica Directory Github Releases page](https://github.com/friendica/friendica-directory/releases), you can find the latest source archive named `friendica-directory-<version>.zip`.

Simply unpack the archive in `/path/to/friendica-directory`, the dependencies are already included.

## Set up a database and a user

Friendica Directory supports MariaDB as a database provider. [Getting started with MariaDB](https://mariadb.com/get-started-with-mariadb/).

Once you have MariaDB installed on a given host, you need to create a database structure and a user with privileges on it.

Sample commands from the MariaDB console for a local install:
```sql
> CREATE DATABASE `friendica-directory`;

> GRANT ALL ON `friendica-directory`.* TO 'friendica-directory'@'localhost' IDENTIFIED BY "password";
```

## 2. Initialize database schema

Using the details gathered from the previous step, follow the instructions in the Friendica Directory Install Wizard.

```
cd /path/to/friendica-directory
bin/console install
```

## 3. Configure your web server

The document root of Friendica Directory is `/public`.

### Apache

Friendica Directory requires `mod_rewrite` to be enabled.

In your Virtual Host file, set your document root as follow:

```
DocumentRoot /path/to/friendica-directory/public/
```

### Nginx
Include this line your nginx config file.

<<<<<<< master
```
root /path/to/friendica-directory/public;
```

## 4. Set up the background task

Friendica Directory relies on a background task running every minute to keep the directory up to date.

On Linux, you can set it up with Crontab, a popular background task scheduler. [Getting started with Crontab](http://www.adminschoice.com/crontab-quick-reference).

Add this line to your crontab:
```
* * * * * cd /path/to/friendica-directory && php bin/cron.php
```

## 5. Seed your directory

Your directory is ready, but empty. To start filling it, you can:
- Set your host name as the main directory in [Friendica](https://github.com/friendica/friendica)'s admin settings.
- Add existing directories in your polling queue: `bin/console directory-add https://dir.friendica.social`.
