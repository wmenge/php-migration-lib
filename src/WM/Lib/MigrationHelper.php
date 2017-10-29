<?php namespace WM\Lib\Migration;

/**
 * Utility class to aid in db vendor independant database migration
 *
 * Usage examples:
 * ===============
 *
 * Creating initial database
 * -------------------------
 * 
 * ORM::configure($configuration);
 *
 * // $migrationScripts => an array of SQL scripts to create and alter db structure
 * // 
 * $helper = new MigrationHelper($migrationScripts, $dropTablesScript, $migrationTableScript);
 * $helper->createDatabase();
 * 
 * 
 * ORM::configure($configuration);
 *
 * $helper = new MigrationHelper($migrationScripts, $dropTablesScript, $migrationTableScript);
 * $helper->migrateDatabase();
 */
class MigrationHelper
{
    private $_migrationScripts = [];
    private $_dropTablesScript = null;
    private $_migrationTableScript = null;

    /**
     * Constructor
     * @param array of migration scripts (filenames)
     * @param string drop table script (filename)
     * @param string migration table create script (file location)
     */
    public function __construct(array $migrationScripts, $dropTablesScript, $migrationTableScript)
    {
        $this->_migrationScripts = $migrationScripts;
        $this->_dropTablesScript = $dropTablesScript;
        $this->_migrationTableScript = $migrationTableScript;
    }

    /**
     * (Re-)creates database according to supplied scripts
     * @return [type]
     */
    public function createDatabase()
    {
        $this->deleteDatabase();
        
        $this->createMigrationTable();

        // For initial setup, just run all scripts
        foreach ($this->_migrationScripts as $version => $script) {
            $this->runSqlScript($script);
            $this->createMigrationRecord($version);
        }
    }

    /**
     *  Migrates database from current version to highest available version
     */
    public function migrateDatabase()
    {
        $currentVersion = $this->getCurrentVersion();

        $this->createMigrationTable();

        foreach ($this->_migrationScripts as $version => $script) {
            if ($version > $currentVersion) {
                $this->runSqlScript($script);
                $this->createMigrationRecord($version);
            }
        }
    }

    /**
     * Deletes db according to delete script
     * @return [type]
     */
    public function deleteDatabase()
    {
        $this->runSqlScript($this->_dropTablesScript);
    }

    /**
     * Checks wether migration is needed (Compares current db version as
     * stored in migration table with highest available script version)
     * @return boolean
     */
    public function isMigrationNeeded()
    {
        return $this->getCurrentVersion() < $this->getHighestVersion();
    }

    /**
     * Gets current db version according to migration table
     * @return numeric db version
     */
    private function getCurrentVersion()
    {
        try {
            //return \ORM::for_table('migration')->max('version');
            return $
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get highest available db version
     * @return numeric db version
     */
    private function getHighestVersion()
    {
        $versions = array_keys($this->_migrationScripts);
        return max($versions);
    }

    /**
     * Creates migration table according to script
     * @return [type]
     */
    private function createMigrationTable()
    {
        $this->runSqlScript($this->_migrationTableScript);
    }

    /**
     * Runs an SQL script
     * @param  string script
     * @return [type]
     */
    private function runSqlScript($script)
    {
        $sql = @file_get_contents($script);
        if ($sql === false) {
            throw new \Exception('Script file \'' . $script. '\' not found', 1);
        }
        
        \ORM::get_db()->exec($sql);
    }

    /**
     * Creates a migration record for given version
     * @param  integer version
     * @return [type]
     */
    private function createMigrationRecord($version)
    {
        $migration = \ORM::for_table('migration')->create();
        $migration->version = $version;
        $migration->executed = time();
        $migration->save();
    }
}
