BEGIN;
CREATE TABLE temp_server_alias AS SELECT * FROM server_alias GROUP BY alias;
DROP TABLE server_alias;
RENAME TABLE temp_server_alias TO server_alias;
CREATE UNIQUE INDEX server_alias_alias_uindex ON server_alias (alias);
COMMIT;
