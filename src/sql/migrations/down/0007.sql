BEGIN;
ALTER TABLE `profile` DROP KEY `profile-ft`;
ALTER TABLE `profile` ADD FULLTEXT KEY `profile-ft` (`name`, `pdesc`, `profile_url`, `locality`, `region`, `country`);
COMMIT;