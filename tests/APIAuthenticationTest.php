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
    public function testAPIKeys($key,$expected)
    {

        //We don't need the URI to test the keys. But, we do need it for the constructor.
        $options['uri'] = '';

        $options['pdo'] = self::$pdo;
        $options['credentials'] = ['key' => $key];

        $Auth = new \PHPAnt\Authentication\AuthorizeAPI($options);
        $this->assertSame($Auth->authenticate(), $expected);
    }
    
    /**
     * Data Provider for testAPIKeys
     *
     * @return array
     */
    public function providerAPIKeys()
    {
        return array( [ 'dpzavyngfhgzbawbuhsxvbrgtshncmxgywhtuvzac', false ]  //Not active
                    , [ 'kvthubxvqwpmsucceyzctctbsnrfmtwwnqdfsaaew', false ] //Not active
                    , [ 'vhmrrrqzpnhsyacfuaayfksrvqtsvwarenfvcvvrg', true  ] //Works
                    , [ 'zpfdymmfywzepfzugrzdrxvmcacddwgdkpggztpxq', false ] //Not active
                    , [ 'cgmckutavuhbdqhmkwgekqhrdxhqyadagqctgwtcq', true  ] //Works
                    , [ 'gentxyezqtxuuafgkhhmrdawgmstarv1wfaueeuuy', false ] //Doesn't exist
                    , [ 'gentxyezqtxuuafgkhhmrdawgmstarv1aueeuuy',   false ] //Doesn't exist, too short.
                    );
    }
}