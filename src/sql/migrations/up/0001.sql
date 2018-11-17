BEGIN;
ALTER TABLE `profile` ADD `language` VARCHAR(30) NULL AFTER `account_type`;

ALTER TABLE `server` ADD `language` VARCHAR(30) NULL AFTER `name`;
COMMIT;
