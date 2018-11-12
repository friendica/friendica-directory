ALTER TABLE `server` ADD `language` VARCHAR(30) NULL AFTER `name`;

ALTER TABLE `profile` ADD `language` VARCHAR(30) NULL AFTER `account_type`;
