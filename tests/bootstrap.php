<?php namespace WM\Lib\Migration;

class ORMMock
{
    private static $_mock;
    
    public function __construct()
    {
    }

    public static function set_mock($mock)
    {
        self::$_mock = $mock;
    }

    public static function get_db()
    {
        return self::$_mock;
    }

    public static function for_table()
    {
        return self::$_mock;
    }
}

// controls behaviour of mocked file_get_contents function
$mock_file_get_contents = true;

function file_get_contents($filename)
{
    global $mock_file_get_contents;

    if (!$mock_file_get_contents) {
        // return real result
        return \file_get_contents($filename);
    }

    // return mocked result
    if ($filename == 'invalidFilename') {
        return false;
    } else {
        return $filename;
    }
}

// Mocking standard time function to be able to compare DB content
function time()
{
    return 0;
}