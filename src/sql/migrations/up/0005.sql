BEGIN;
UPDATE `server` SET `ssl_state` = 0 WHERE `ssl_state` IS NULL;

ALTER TABLE `server` MODIFY `ssl_state` tinyint(1) DEFAULT 0 NOT NULL;
COMMIT;
