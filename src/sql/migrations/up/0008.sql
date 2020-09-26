BEGIN;
ALTER TABLE `profile` DROP KEY `profile-ft`;
ALTER TABLE `profile` ADD FULLTEXT KEY `profile-ft` (`name`, `pdesc`, `username`, `locality`, `region`, `country`);
COMMIT;
