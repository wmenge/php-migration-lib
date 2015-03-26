Simple PHP Database migration library
=====================================

This utility class aids in managing and performing simple database migrations for your PHP project. The class itself is database agnostic, it will simply manage and run the provided SQL Scripts.

Usage example
-------------

migration-1-sqlite.sql:
````
CREATE TABLE "table_1" ( "field_1" integer NOT NULL );
````
migration-2-sqlite.sql:
````
ALTER TABLE "table_1" ADD COLUMN "field_2" integer;
CREATE TABLE "table_2" ( "field_1" integer NOT NULL );
````
PHP Code:
````php
// Create helper instance, providing all needed scripts
$helper = new \WM\Lib\MigrationHelper(
    [
        1 => '/files/migration-1-sqlite.sql',
        2 => '/files/migration-2-sqlite.sql'
    ],
    '/files/migration-drop-tables-sqlite.sql',
    '/files/migration-table-sqlite.sql'
);
// Perform actual migration
// The helper class will consult a migration table to check
// which migration scripts need to run
$helper->migrateDatabase();
````
Simply provide the helper class with a list of SQL Migration scripts. The helper class uses a migration table to keep track of scripts that have already been executed. When the migrateDatabase() operation is executed, only the previously unprocessed scripts are being executed.

