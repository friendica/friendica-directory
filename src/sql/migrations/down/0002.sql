BEGIN;
ALTER table `profile` DROP KEY `language`;

ALTER table `server` DROP KEY `language`;
COMMIT;
