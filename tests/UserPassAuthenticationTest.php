<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\DbUnit\TestCaseTrait;

$dependencies = [ 'tests/test_top.php'
                ];

foreach($dependencies as $d) {
    require_once($d);
}

class UserPassAuthenticationTest extends TestCase
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
     * @dataProvider providerUsePassCombos
     * @covers APIKeys
     * @return void
     */
    public function testUserPassCombos($user, $pass, $hash, $expected)
    {

        //We don't need the URI to test the keys. But, we do need it for the constructor.
        $options['uri'] = '';
        $options['pdo'] = self::$pdo;
        $options['credentials'] = ['username' => $user, 'password' => $pass];
        $options['AppEngine'] = getMyAppEngine(getDefaultOptions());

        $Auth = new \PHPAnt\Authentication\AuthorizePageview($options);
        $this->assertSame($Auth->authenticate(), $expected);
    }
    
    /**
     * Data Provider for testUserPassCombos
     *
     * @return array
     */
    public function providerUsePassCombos()
    {
        return array( ['itatartrate@precompounding.co.uk'   , 'OCUOpvme'        , '$2y$10$AcM4EisYquZeHVIiEG4MuOQ/6J0gmv/HhSJv0Q.zmg4IbhKHl8uJW', 2     ]// 1   Should work. Valid. (Hash may not be though, and that's OK).
                    , ['tephramancy@gatewise.co.uk'         , 'bPTFZUWbkQlcJJc' , '$2y$10$UH9xeapeoPaX5MQH/qa9JuPD/INLoV2WxyyoQcUeFTV4fzqNwyD4u', 3     ]// 2   Should work. Valid. (Hash may not be though, and that's OK).
                    , ['unsanctimoniousness@coenoecic.edu'  , 'yNKFJTLLpJyP'    , '$2y$10$oZf3gnMt612tte0Qyx0lvueBEEevI9ATfXIB6N98K1EWg2g6rtL1q', 4     ]// 3   Should work. Valid. (Hash may not be though, and that's OK).
                    , ['extranatural@possumwood.net'        , 'bDGSaFHxAwA'     , '$2y$10$lzrIpV1V74osSLizZ8yGt.pDY9tnrd.TNVVwhdgj/ZFVzamdAbP2m', 5     ]// 4   Should work. Valid. (Hash may not be though, and that's OK).
                    , ['ureteral@amorphy.edu'               , 'AahymJIyJWLTuA'  , '$2y$10$3jjEwJMD5/nXntlPoatfTudzX4raVpb72PsHw3239NQdOvChAydO6', 6     ]// 5   Should work. Valid. (Hash may not be though, and that's OK).
                    , ['tatartrate@precompounding.co.uk'    , 'OCUOpvme'        , '$2y$10$AcM4EisYquZeHVIiEG4MuOQ/6J0gmv/HhSJv0Q.zmg4IbhKHl8uJW', false ]// 6   Wrong Email address. (Does not exist) 
                    , ['ephramancy@gatewise.co.uk'          , 'bPTFZUWbkQlcJJc' , '$2y$10$UH9xeapeoPaX5MQH/qa9JuPD/INLoV2WxyyoQcUeFTV4fzqNwyD4u', false ]// 7   Wrong Email address. (Does not exist) 
                    , ['nsanctimoniousness@coenoecic.edu'   , 'yNKFJTLLpJyP'    , '$2y$10$oZf3gnMt612tte0Qyx0lvueBEEevI9ATfXIB6N98K1EWg2g6rtL1q', false ]// 8   Wrong Email address. (Does not exist) 
                    , ['xtranatural@possumwood.net'         , 'bDGSaFHxAwA'     , '$2y$10$lzrIpV1V74osSLizZ8yGt.pDY9tnrd.TNVVwhdgj/ZFVzamdAbP2m', false ]// 9   Wrong Email address. (Does not exist) 
                    , ['reteral@amorphy.edu'                , 'ahymJIyJWLTuA'  , '$2y$10$3jjEwJMD5/nXntlPoatfTudzX4raVpb72PsHw3239NQdOvChAydO6',  false ]// 10  Wrong Email address. (Does not exist) 
                    , ['itatartrate@precompounding.co.uk'   , 'CUOpvme'        , '$2y$10$AcM4EisYquZeHVIiEG4MuOQ/6J0gmv/HhSJv0Q.zmg4IbhKHl8uJW',  false ]// 11  Wrong password 
                    , ['tephramancy@gatewise.co.uk'         , 'PTFZUWbkQlcJJc' , '$2y$10$UH9xeapeoPaX5MQH/qa9JuPD/INLoV2WxyyoQcUeFTV4fzqNwyD4u',  false ]// 12  Wrong password 
                    , ['unsanctimoniousness@coenoecic.edu'  , 'NKFJTLLpJyP'    , '$2y$10$oZf3gnMt612tte0Qyx0lvueBEEevI9ATfXIB6N98K1EWg2g6rtL1q',  false ]// 13  Wrong password 
                    , ['extranatural@possumwood.net'        , 'DGSaFHxAwA'     , '$2y$10$lzrIpV1V74osSLizZ8yGt.pDY9tnrd.TNVVwhdgj/ZFVzamdAbP2m',  false ]// 14  Wrong password 
                    , ['ureteral@amorphy.edu'               , 'ahymJIyJWLTuA'  , '$2y$10$3jjEwJMD5/nXntlPoatfTudzX4raVpb72PsHw3239NQdOvChAydO6',  false ]// 15  Wrong password 
                    ); 
    }
}