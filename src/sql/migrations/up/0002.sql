ALTER table `profile` DROP INDEX `profile-ft`;
ALTER table `profile` ADD FULLTEXT KEY `profile-ft` (`name`, `pdesc`, `profile_url`, `locality`, `region`, `country`, `tags`);
