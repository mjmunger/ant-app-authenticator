<?php
use PHPUnit\Framework\TestCase;
use PHPUnit\DbUnit\TestCaseTrait;

$dependencies = [ 'tests/test_top.php'
                ];

foreach($dependencies as $d) {
    require_once($d);
}

class APIAuthenticationTest extends TestCase
{
    use TestCaseTrait;
    
    private $conn       = NULL;
    static private $pdo = NULL;

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

    /**
     * tests API keys to determine if they are valid.
     *
     * @dataProvider providerAPIKeys
     * @covers APIKeys
     * @return void
     */
    public function testAPIKeys($key,$expectedAuthorized, $keyEnabled, $keyExists)
    {

        //We don't need the URI to test the keys. But, we do need it for the constructor.
        $options['uri'] = '';

        $options['pdo'] = self::$pdo;
        $options['credentials'] = ['key' => $key];

        $Auth = new \PHPAnt\Authentication\AuthorizeAPI($options);

        $this->assertSame( $Auth->authenticate() , $expectedAuthorized );
        $this->assertSame( $Auth->keyEnabled     , $keyEnabled         );
        $this->assertSame( $Auth->keyExists      , $keyExists          );
    }
    
    /**
     * Data Provider for testAPIKeys
     *
     * @return array
     */
    public function providerAPIKeys()
    {
        /**
         * Testing table:
         *    ANX  //Authorized, eNabled and eXists
         * 1. TTT  //Key exists, is enabled, therefore authorized.
         * 2. TTF  //Not possible. T1 can only be true if T2 and T3 are true.
         * 3. TFT  //Not possible. T1 can only be true if T2 and T3 are true.
         * 4. TFF  //Not possible. T1 can only be true if T2 and T3 are true.
         * 5. FTT  //Not possible. When it exists and is enabled, then it should be authorized.
         * 6. FTF  //Not possible. Keys that don't exist cannot be enabled (or authorized).
         * 7. FFT  //Key exists, but is not enabled so therefore, not authorized.
         * 8. FFF  //Key doesn't exist, so cannot be enabled, and cannot be authorized.
         * 
         * We only need to test 1, 7, and 8.
         */
                    //key                                         //authorized   //enabled    //exists         
        return  [ [ 'vhmrrrqzpnhsyacfuaayfksrvqtsvwarenfvcvvrg' , true         , true       , true      ] // #1 above.
                , [ 'zpfdymmfywzepfzugrzdrxvmcacddwgdkpggztpxq' , false        , false      , true      ] // #7 above.
                , [ 'gentxyezqtxuuafgkhhmrdawgmstarv1wfaueeuuy' , false        , false      , false     ] // #8 above.
                , [ 'gentxyezqtxuuafgkhhmrdawgmstarv1aueeuuy'   , false        , false      , false     ] // Malformed.
                ];
                
                    
    }
}