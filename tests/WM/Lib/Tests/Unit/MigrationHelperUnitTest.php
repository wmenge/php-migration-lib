<?php namespace WM\Lib\Tests\Unit;

include __DIR__ . '/../../../../../src/WM/Lib/Migration/MigrationHelper.php';

class MigrationHelperUnitTest extends \PHPUnit_Framework_TestCase
{
    public $mock = null;

    protected function setUp()
    {
        if (!class_exists('\ORM')) {
            class_alias('\WM\Lib\Migration\ORMMock', '\ORM');
        }

        global $mock_file_get_contents;
    
        $mock_file_get_contents = true;

        $this->mock = $this->getMockBuilder('SomeClass')
                     ->setMethods(array('exec', 'create', 'save', 'max'))
                     ->getMock();

        $this->mock->method('create')
             ->willReturn($this->mock);

        \ORM::set_mock($this->mock);
    }

    /**
     * @expectedException Exception
     */
    public function testCreateDatabaseWithInvalidScriptFiles()
    {
        $helper = new \WM\Lib\Migration\MigrationHelper(['invalidFilename', 'invalidFilename'], 'invalidFilename', 'invalidFilename');
        $helper->createDatabase();
    }

    public function testCreateDatabaseWithValidScriptFiles()
    {
        $this->mock->expects($this->exactly(4))
             ->method('exec')
             ->withConsecutive(
                 $this->equalTo('drop'),
                 $this->equalTo('create'),
                 $this->equalTo('v1'),
                 $this->equalTo('v2')
             );

        $this->mock->expects($this->exactly(2))->method('create');
        $this->mock->expects($this->exactly(2))->method('save');

        $helper = new \WM\Lib\Migration\MigrationHelper([1 => 'v1', 2 => 'v2'], 'drop', 'create');
        $helper->createDatabase();
    }

    public function testHighestVersion()
    {
        $helper = new \WM\Lib\Migration\MigrationHelper([1 => 'v1', 2 => 'v2'], 'drop', 'create');
        $this->assertEquals(2, $helper->getHighestVersion());
    }

    public function testCurrentVersion()
    {
        $current = 2; // DB contains 2 as current version

        $this->mock->method('max')
             ->willReturn($current);

        $helper = new \WM\Lib\Migration\MigrationHelper([1 => 'v1', 2 => 'v2'], 'drop', 'create');
        $helper->createDatabase();
        $this->assertEquals($current, $helper->getCurrentVersion());
    }

    public function testMigrationNeeded()
    {
        $current = 1; // DB contains 1 as current version

        $this->mock->method('max')
             ->willReturn($current);

        // scripts array contains 2 as highest version, migration needed
        $helper = new \WM\Lib\Migration\MigrationHelper([1 => 'v1', 2 => 'v2'], 'drop', 'create');
        $helper->createDatabase();
        $this->assertEquals(true, $helper->isMigrationNeeded());
    }


    public function testMigrationNotNeeded()
    {
        $current = 2; // DB contains 2 as current version

        $this->mock->method('max')
             ->willReturn($current);

        // scripts array contains 2 as highest version, migration not needed
        $helper = new \WM\Lib\Migration\MigrationHelper([1 => 'v1', 2 => 'v2'], 'drop', 'create');
        $helper->createDatabase();
        $this->assertEquals(false, $helper->isMigrationNeeded());
    }

    /**
     * @expectedException Exception
     */
    public function testMigrateDatabaseWithInvalidScriptFiles()
    {
        $helper = new \WM\Lib\Migration\MigrationHelper(['invalidFilename', 'invalidFilename'], 'invalidFilename', 'invalidFilename');
        $helper->migrateDatabase();
    }

    public function testMigrateDatabaseWithValidScriptFiles()
    {
        $this->mock->expects($this->exactly(3))
             ->method('exec')
             ->withConsecutive(
                 $this->equalTo('create'),
                 $this->equalTo('v1'),
                 $this->equalTo('v2')
             );

        $this->mock->expects($this->exactly(2))->method('create');
        $this->mock->expects($this->exactly(2))->method('save');

        $helper = new \WM\Lib\Migration\MigrationHelper([1 => 'v1', 2 => 'v2'], 'drop', 'create');
        $helper->migrateDatabase();
    }

    /**
     * @expectedException Exception
     */
    public function testDeleteDatabaseWithInvalidScriptFiles()
    {
        $helper = new \WM\Lib\Migration\MigrationHelper(['invalidFilename', 'invalidFilename'], 'invalidFilename', 'invalidFilename');
        $helper->deleteDatabase();
    }

    public function testDeleteDatabaseWithValidScriptFiles()
    {
        $this->mock->expects($this->once())
             ->method('exec')
             ->with($this->equalTo('drop'));

        $helper = new \WM\Lib\Migration\MigrationHelper([1 => 'v1', 2 => 'v2'], 'drop', 'create');
        $helper->deleteDatabase();
    }

    public function testMigrationScenario()
    {
        $this->mock->expects($this->exactly(6))
             ->method('exec')
             ->withConsecutive(
                 $this->equalTo('drop'),
                 $this->equalTo('create'),
                 $this->equalTo('v1'),
                 $this->equalTo('v2'),
                 $this->equalTo('create'),
                 $this->equalTo('v3')
             );

        $this->mock->expects($this->exactly(3))->method('create');
        $this->mock->expects($this->exactly(3))->method('save');

        $helper = new \WM\Lib\Migration\MigrationHelper([1 => 'v1', 2 => 'v2'], 'drop', 'create');
        $helper->createDatabase();

        $current = 2; // DB contains 1 as current version

        $this->mock->method('max')
             ->willReturn($current);

        $helper = new \WM\Lib\Migration\MigrationHelper([1 => 'v1', 2 => 'v2', 3 => 'v3'], 'drop', 'create');
        $helper->migrateDatabase();
    }
}
