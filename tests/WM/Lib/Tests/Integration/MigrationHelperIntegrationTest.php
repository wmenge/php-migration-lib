<?php namespace WM\Lib\Tests\Unit;

/**
 * Integration test with SQLite in-memory db
 *
 * By running the test in a separate process we can use the actual
 * ORM class in the integration test and a mock (\WM\Lib\ORMMock)
 * in the unit test
 *
 * @runTestsInSeparateProcesses
 */
class MigrationHelperIntegrationTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        global $mock_file_get_contents;
        $mock_file_get_contents = false;

        require_once __DIR__ . '/../../../../../vendor/autoload.php';

        \ORM::configure([ 'connection_string' => 'sqlite::memory:' ]);
    }

    /**
     * @codeCoverageIgnore
     */
    public function testCreateDatabase()
    {
        // Create initial database
        $helper = new \WM\Lib\Migration\MigrationHelper(
            [
                1 => __DIR__ . '/files/migration-1-sqlite.sql',
                2 => __DIR__ . '/files/migration-2-sqlite.sql'
            ],
            __DIR__ . '/files/migration-drop-tables-sqlite.sql',
            __DIR__ . '/files/migration-table-sqlite.sql'
        );

        $helper->createDatabase();

        // Assert migration records
        $expected = [
            [ 'id' => 1, 'version' => 1, 'executed' => 0 ],
            [ 'id' => 2, 'version' => 2, 'executed' => 0 ]
        ];

        $this->assertEquals($expected, \ORM::for_table('migration')->find_array());

        // Assert DB Structure
        $tables =
            \ORM::for_table('sqlite_master')
            ->select('name')
            ->where('type', 'table')
            ->where_not_equal('name', 'sqlite_sequence')
            ->find_array();

        $expected = [
            [ 'name' => 'migration' ],
            [ 'name' => 'table_1' ],
            [ 'name' => 'table_2' ],
        ];

        $this->assertEquals($expected, $tables);

        $result = \ORM::get_db()->query('PRAGMA table_info(migration)');
        $result->setFetchMode(\PDO::FETCH_ASSOC);
        
        $expected = [
            [ 'cid' => 0, 'name' => 'id',       'type' => 'integer', 'notnull' => 1, 'dflt_value' => null, 'pk' => 1 ],
            [ 'cid' => 1, 'name' => 'version',  'type' => 'text',    'notnull' => 1, 'dflt_value' => null, 'pk' => 0 ],
            [ 'cid' => 2, 'name' => 'executed', 'type' => 'integer', 'notnull' => 1, 'dflt_value' => null, 'pk' => 0 ]
        ];

        $this->assertEquals($expected, $result->fetchAll());

        
        $result = \ORM::get_db()->query('PRAGMA table_info(table_1)');
        $result->setFetchMode(\PDO::FETCH_ASSOC);

        $expected = [
            [ 'cid' => 0, 'name' => 'field_1',  'type' => 'integer', 'notnull' => 1, 'dflt_value' => null, 'pk' => 0 ],
            [ 'cid' => 1, 'name' => 'field_2',  'type' => 'integer', 'notnull' => 0, 'dflt_value' => null, 'pk' => 0 ],
        ];

        $this->assertEquals($expected, $result->fetchAll());

        $result = \ORM::get_db()->query('PRAGMA table_info(table_2)');
        $result->setFetchMode(\PDO::FETCH_ASSOC);
        
        $expected = [
            [ 'cid' => 0, 'name' => 'field_1',  'type' => 'integer', 'notnull' => 1, 'dflt_value' => null, 'pk' => 0 ],
        ];

        $this->assertEquals($expected, $result->fetchAll());
    }

    /**
     * @codeCoverageIgnore
     */
    public function testMigrateDatabase()
    {
        // Create initial database
        $helper = new \WM\Lib\Migration\MigrationHelper(
            [
                1 => __DIR__ . '/files/migration-1-sqlite.sql',
            ],
            __DIR__ . '/files/migration-drop-tables-sqlite.sql',
            __DIR__ . '/files/migration-table-sqlite.sql'
        );

        $helper->createDatabase();

        // Assert migration records
        $expected = [[ 'id' => 1, 'version' => 1, 'executed' => 0 ]];

        $this->assertEquals($expected, \ORM::for_table('migration')->find_array());

        // Assert DB Structure
        $tables =
            \ORM::for_table('sqlite_master')
            ->select('name')
            ->where('type', 'table')
            ->where_not_equal('name', 'sqlite_sequence')
            ->find_array();

        $expected = [
            [ 'name' => 'migration' ],
            [ 'name' => 'table_1' ],
        ];

        $this->assertEquals($expected, $tables);

        $result = \ORM::get_db()->query('PRAGMA table_info(migration)');
        $result->setFetchMode(\PDO::FETCH_ASSOC);
        
        $expected = [
            [ 'cid' => 0, 'name' => 'id',       'type' => 'integer', 'notnull' => 1, 'dflt_value' => null, 'pk' => 1 ],
            [ 'cid' => 1, 'name' => 'version',  'type' => 'text',    'notnull' => 1, 'dflt_value' => null, 'pk' => 0 ],
            [ 'cid' => 2, 'name' => 'executed', 'type' => 'integer', 'notnull' => 1, 'dflt_value' => null, 'pk' => 0 ]
        ];

        $this->assertEquals($expected, $result->fetchAll());

        $result = \ORM::get_db()->query('PRAGMA table_info(table_1)');
        $result->setFetchMode(\PDO::FETCH_ASSOC);
        
        $expected = [
            [ 'cid' => 0, 'name' => 'field_1',  'type' => 'integer', 'notnull' => 1, 'dflt_value' => null, 'pk' => 0 ],
        ];

        $this->assertEquals($expected, $result->fetchAll());

        // Perform database migration
        $helper = new \WM\Lib\Migration\MigrationHelper(
            [
                1 => __DIR__ . '/files/migration-1-sqlite.sql',
                2 => __DIR__ . '/files/migration-2-sqlite.sql'
            ],
            __DIR__ . '/files/migration-drop-tables-sqlite.sql',
            __DIR__ . '/files/migration-table-sqlite.sql'
        );
        $helper->migrateDatabase();

        // Assert migration records
        $expected = [
            [ 'id' => 1, 'version' => 1, 'executed' => 0 ],
            [ 'id' => 2, 'version' => 2, 'executed' => 0 ]
        ];

        $this->assertEquals($expected, \ORM::for_table('migration')->find_array());

        // Assert DB Structure
        $tables =
            \ORM::for_table('sqlite_master')
            ->select('name')
            ->where('type', 'table')
            ->where_not_equal('name', 'sqlite_sequence')
            ->find_array();

        $expected = [
            [ 'name' => 'migration' ],
            [ 'name' => 'table_1' ],
            [ 'name' => 'table_2' ],
        ];

        $this->assertEquals($expected, $tables);

        $result = \ORM::get_db()->query('PRAGMA table_info(migration)');
        $result->setFetchMode(\PDO::FETCH_ASSOC);
        
        $expected = [
            [ 'cid' => 0, 'name' => 'id',       'type' => 'integer', 'notnull' => 1, 'dflt_value' => null, 'pk' => 1 ],
            [ 'cid' => 1, 'name' => 'version',  'type' => 'text',    'notnull' => 1, 'dflt_value' => null, 'pk' => 0 ],
            [ 'cid' => 2, 'name' => 'executed', 'type' => 'integer', 'notnull' => 1, 'dflt_value' => null, 'pk' => 0 ]
        ];

        $this->assertEquals($expected, $result->fetchAll());

        
        $result = \ORM::get_db()->query('PRAGMA table_info(table_1)');
        $result->setFetchMode(\PDO::FETCH_ASSOC);
        
        $expected = [
            [ 'cid' => 0, 'name' => 'field_1',  'type' => 'integer', 'notnull' => 1, 'dflt_value' => null, 'pk' => 0 ],
            [ 'cid' => 1, 'name' => 'field_2',  'type' => 'integer', 'notnull' => 0, 'dflt_value' => null, 'pk' => 0 ],
        ];

        $this->assertEquals($expected, $result->fetchAll());

        $result = \ORM::get_db()->query('PRAGMA table_info(table_2)');
        $result->setFetchMode(\PDO::FETCH_ASSOC);
        
        $expected = [
            [ 'cid' => 0, 'name' => 'field_1',  'type' => 'integer', 'notnull' => 1, 'dflt_value' => null, 'pk' => 0 ],
        ];

        $this->assertEquals($expected, $result->fetchAll());
    }

    /**
     * @codeCoverageIgnore
     */
    public function testMigrateDatabaseRetainsContent()
    {
        // Create initial database
        $helper = new \WM\Lib\Migration\MigrationHelper(
            [
                1 => __DIR__ . '/files/migration-1-sqlite.sql',
            ],
            __DIR__ . '/files/migration-drop-tables-sqlite.sql',
            __DIR__ . '/files/migration-table-sqlite.sql'
        );

        $helper->createDatabase();

        // Creata record
        $record = \ORM::for_table('table_1')->create();
        $record->field_1 = 42;
        $record->save();

        // Assert existence of records
        $expected = [[ 'field_1' => 42 ]];

        $this->assertEquals($expected, \ORM::for_table('table_1')->find_array());

        // Perform database migration
        $helper = new \WM\Lib\Migration\MigrationHelper(
            [
                1 => __DIR__ . '/files/migration-1-sqlite.sql',
                2 => __DIR__ . '/files/migration-2-sqlite.sql'
            ],
            __DIR__ . '/files/migration-drop-tables-sqlite.sql',
            __DIR__ . '/files/migration-table-sqlite.sql'
        );
        $helper->migrateDatabase();

        // Assert existence of records
        $expected = [['field_1' => 42, 'field_2' => null ]];

        $this->assertEquals($expected, \ORM::for_table('table_1')->find_array());
    }

}
