BEGIN;
ALTER table `profile` ADD FULLTEXT KEY `tags` (`tags`);
COMMIT;
