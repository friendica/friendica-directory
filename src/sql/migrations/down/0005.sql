BEGIN;
ALTER TABLE `server` ADD `path` varchar(190) NOT NULL DEFAULT '' AFTER `base_url`;
COMMIT;
