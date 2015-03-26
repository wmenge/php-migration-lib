PRAGMA foreign_keys = true;
PRAGMA ignore_check_constraints = true;

ALTER TABLE "table_1" ADD COLUMN "field_2" integer;

DROP TABLE IF EXISTS "table_2";
CREATE TABLE "table_2" (
     "field_1" integer NOT NULL
);

PRAGMA foreign_keys = false;
PRAGMA ignore_check_constraints = false;
