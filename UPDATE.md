# Friendica Directory Update Instructions

## 1. Update the source code

If you installed Friendica Directory in `/path/to/friendica-directory`.

### Using Git

```
cd /path/to/friendica-directory
git pull
composer install
```

### Using an archive

1. Create a temporary folder to unpack the new archive.
2. Copy your old `config/local.json` to the new folder.
3. Swap the folder names.
4. Remove the temporary folder.

Sample Linux commands:
```
cd /path/to
mkdir friendica-directory-new
unzip friendica-<version>.zip friendica-directory-new
cp friendica-directory/config/local.json friendica-directory-new/config
mv friendica-directory friendica-directory-old
mv friendica-directory-new friendica-directory
rm -r friendica-directory-old
```

## 2. Update the database structure

The database structure may have changed since the last update, fortunately a console command allows to run the migration scripts up to the latest version:

```
cd /path/to/friendica-directory
bin/console dbupdate
```

### Known issues

Before version 2.1, updating the database schema was impossible because the `status` column value of the `migration_version` table was incorrectly set to `partial up` instead of `complete`.  
Updating from 2.0.x, changing the value allows to update the database schema.
