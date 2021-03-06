<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\DbUnit\TestCaseTrait;

class KeyAuthenticationTest extends TestCase
{
    use TestCaseTrait;
    private $conn       = NULL;
    static private $pdo = NULL;

    public static function setUpBeforeClass() {
        $dependencies = [ 'classes/iAuthorizationRequest.interface.php'
            , 'classes/AuthBfwBase.class.php'
            , 'classes/AuthorizationRequest.class.php'
            , 'classes/AuthorizePageview.class.php'
            , 'classes/AuthCLI.class.php'
            , 'classes/AuthenticationWhitelistManager.class.php'
            , 'classes/RequestFactory.class.php'
            , 'classes/AuthenticationRouter.class.php'
            , 'classes/AuthEnvFactory.class.php'
            , 'classes/AuthorizeAPI.class.php'
            , 'classes/CredentialStorage.class.php'
            , 'classes/AuthMobile.class.php'
            , 'classes/AuthWeb.class.php'
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

    /**
     * Tests user keys to see if they are valid.
     *
     * @dataProvider providerKeys
     * @covers authenticateKey
     * @return void
     */
    public function testUserTokens($token, $expectedAuth, $expectedUserId)
    {

        //We don't need the URI to test the keys. But, we do need it for the constructor.
        $options['uri']     = '';
        $options['pdo']     = self::$pdo;
        $options['cookies'] = ['users_token' => $token];

        $Auth = new \PHPAnt\Authentication\AuthorizePageview($options);
        $this->assertSame($Auth->authenticate(), $expectedAuth);
        $this->assertSame($Auth->users_id, $expectedUserId);
    }
    
    /**
     * Data Provider for testUserTokens
     *
     * @return array
     */
    public function providerKeys()
    {
        return array( [ '463f4b5ff3885ebc0d33ee2c5ec732d2af69f6152cefd4becbffb7bf74cd9ac8' , true  , 1     ]
                    , [ 'd6cbec51334a8054a1ef46508bf92badce119bd7fb6441bcb434ebc91f212f9d' , true  , 2     ]
                    , [ 'd48c3659bdf66e2eccce42f4111c95e8505ed3da2706c740890f62daf3f2a213' , true  , 3     ]
                    , [ '309e228a3c7d252f0939d8d0517d04d9b9f874bab4eec4c4218f952edd4d85d9' , true  , 4     ]
                    , [ 'e1261474304406f7a14844227fe4c74261544c2427b5b765ce6225f076879827' , true  , 5     ]
                    , [ '97577059f6d16ea6503739151273b18934916c74873a3f020e749e2c25892cb6' , true  , 6     ]
                    , [ '70800c40b939a576f08334ec47d5976a0bd9a515003518df39f3f0ec175d427'  , false , false ] //too short
                    , [ '0423809fc5f5785af5c08a6fbbd045cc8f883ecded80bd32e522b1b14746ebc9' , false , false ] //wrong
                    , [ ''                                                                 , false , false ] //empty
                    ); 
    }
}