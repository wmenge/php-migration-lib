PRAGMA foreign_keys = false;
PRAGMA ignore_check_constraints = false;

DROP TABLE IF EXISTS "table_1";
CREATE TABLE "table_1" (
	 "field_1" integer NOT NULL
);

PRAGMA foreign_keys = true;
PRAGMA ignore_check_constraints = false;
