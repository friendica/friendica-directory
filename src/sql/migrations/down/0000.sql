BEGIN;
ALTER TABLE `profile` DROP `language`;

ALTER TABLE `server` DROP `language`;
COMMIT;
