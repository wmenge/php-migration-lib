-- ----------------------------
--  Drop all known application tables
-- ----------------------------
PRAGMA foreign_keys = false;

DROP TABLE IF EXISTS "table_1";
DROP TABLE IF EXISTS "table_2";

DROP TABLE IF EXISTS "migration";

PRAGMA foreign_keys = true;