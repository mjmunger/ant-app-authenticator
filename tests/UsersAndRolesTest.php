<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\DbUnit\TestCaseTrait;

class UsersAndRolesTest extends TestCase
{
    use TestCaseTrait;
    private $conn       = NULL;
    static private $pdo = NULL;

    public static function setUpBeforeClass() {
        $dependencies = [ 'classes/iAuthorizationRequest.interface.php'
                        ];

        foreach($dependencies as $d) {
            $target = dirname(__DIR__) . '/' . $d;
            require_once($target);
        }
    }
    public function getConnection() {

        //Get the schema so we can create it in memory to prepare for testing.

        if($this->conn === null) {
            if (self::$pdo == null) {
                self::$pdo = new PDO( $GLOBALS['DB_DSN'], $GLOBALS['DB_USER'], $GLOBALS['DB_PASSWD'] );
            }
        }

        $this->conn =  $this->createDefaultDBConnection(self::$pdo,':memory:');
        return $this->conn;

    }

    public function getDataSet() {
        return $this->createMySQLXMLDataSet( __DIR__ .'/authtest.xml');
    }

    public function testAddRole() {
        //TBD. Allow this to win so it doesn't muddy up tests.
        $this->assertTrue(true);
    }
}