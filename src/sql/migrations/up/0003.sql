BEGIN;
ALTER table `profile` ADD KEY `language` (`language`);

ALTER table `server` ADD KEY `language` (`language`);
COMMIT;
