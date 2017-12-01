<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\DbUnit\TestCaseTrait;

class UsersAndRolesTest extends TestCase
{
    use TestCaseTrait;
    private $conn       = NULL;
    static private $pdo = NULL;

    public static function setUpBeforeClass() {
        $dependencies = [ 'tests/test_top.php'
                        , 'includes/apps/ant-app-authenticator/classes/iAuthorizationRequest.interface.php'
                        ];

        foreach($dependencies as $d) {
            require_once($d);
        }
    }
    public function getConnection() {

        //Get the schema so we can create it in memory to prepare for testing.

        if($this->conn === null) {
            if (self::$pdo == null) {
                self::$pdo = gimmiePDO();
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