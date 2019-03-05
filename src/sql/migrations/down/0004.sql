BEGIN;
ALTER TABLE `server` MODIFY `ssl_state` bit(1) NULL;
COMMIT;