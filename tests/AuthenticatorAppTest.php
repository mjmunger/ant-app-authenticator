<?php

namespace PHPAnt\Core;

use PHPUnit\Framework\TestCase;
use PHPUnit\DbUnit\TestCaseTrait;

$dependencies = [ 'tests/test_top.php'
                , 'includes/apps/ant-app-authenticator/classes/AuthBfwBase.class.php'
                , 'includes/apps/ant-app-authenticator/classes/AuthorizationRequest.class.php'
                , 'includes/apps/ant-app-authenticator/classes/AuthorizePageview.class.php'
                , 'includes/apps/ant-app-authenticator/classes/AuthCLI.class.php'
                , 'includes/apps/ant-app-authenticator/classes/AuthenticationWhitelistManager.class.php'
                , 'includes/apps/ant-app-authenticator/classes/RequestFactory.class.php'
                , 'includes/apps/ant-app-authenticator/classes/AuthenticationRouter.class.php'
                , 'includes/apps/ant-app-authenticator/classes/AuthEnvFactory.class.php'
                , 'includes/apps/ant-app-authenticator/classes/AuthorizeAPI.class.php'
                , 'includes/apps/ant-app-authenticator/classes/CredentialStorage.class.php'
                , 'includes/apps/ant-app-authenticator/classes/AuthMobile.class.php'
                , 'includes/apps/ant-app-authenticator/classes/AuthWeb.class.php'
                , 'includes/apps/ant-app-authenticator/app.php'
                ];

foreach($dependencies as $d) {
    require_once($d);
}

class AuthenticatorAppTest extends TestCase
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
        return $this->createMySQLXMLDataSet( __DIR__ .'/apptestdata.xml');
    }

    /**
     * @dataProvider providerTestUserPass
     */

    public function testUserPass($user,$pass,$expected) {
        //1. Get configs we need

        $options = getDefaultOptions();
        $Configs = getWebConfigs(false,self::$pdo);
        $AppEngine = new AppEngine($Configs,$options);


        $this->assertInstanceOf('PHPAnt\Core\AppEngine', $AppEngine);

        $AppEngine->setVerbosity(10);

        $this->assertSame(10, $AppEngine->verbosity);
        //Make sure our apps are enabled. If this test fails, restore the
        //apptestdata.sql fiel to the database, then use the CLI to enable the
        //correct apps. Finally, use smartdump to re-dump the XML data for
        //this test and run again.

        $appNames = array_keys($AppEngine->enabledApps);
        $this->assertContains('PHPAnt Authenticator', $appNames);

        //Setup the "web" environment so we can test.

        $AppEngine->Configs->Server->Request->uri       = '/foo/baz/';
        $AppEngine->Configs->Server->Request->get_vars  = [];
        $AppEngine->Configs->Server->Request->post_vars = ['username' => $user, 'password' => $pass];
        $AppEngine->Configs->Server->Request->cookies   = [];

        //try to authenticate the user and password.
        $result = $AppEngine->runActions('auth-user');

        if($expected == true) $this->assertSame(strtolower($result['current_user']->users_email), strtolower($user));

        if($expected == false) $this>$this->assertNull($result['current_user']->users_id);

        // var_dump($result);
        // die(__FILE__  . ':' . __LINE__ );
    }

    public function providerTestUserPass() {

                   //Username                //Password         //authenticates
        return  [ ['Santti@indologist.edu' , 'sqqXXA7BAJNE57v'  , true  ]
                , ['SANTTI@INDOLOGIST.EDU' , 'sqqXXA7BAJNE57v'  , true  ]  //Case insensitive.
                , ['SANTTI@INDOLOGIST.EDU' , 'xsqqXXA7BAJNE57v' , false ]  //Invalid pass
                , ['ANTTI@INDOLOGIST.EDU'  , 'sqqXXA7BAJNE57v'  , false ]  //Invalid user
                ];
    }

    /**
     * @dataProvider providerTestAPIAuthentication
     **/

    public function testAPIAuthentication($keyVar, $key, $expected) {
        //1. Get configs we need

        $options = getDefaultOptions();
        $Configs = getWebConfigs(false,self::$pdo);
        $AppEngine = new AppEngine($Configs,$options);

        $this->assertInstanceOf('PHPAnt\Core\AppEngine', $AppEngine);

        $AppEngine->setVerbosity(10);

        $this->assertSame(10, $AppEngine->verbosity);

        //Make sure our apps are enabled. If this test fails, restore the
        //apptestdata.sql fiel to the database, then use the CLI to enable the
        //correct apps. Finally, use smartdump to re-dump the XML data for
        //this test and run again.

        $appNames = array_keys($AppEngine->enabledApps);
        $this->assertContains('PHPAnt Authenticator', $appNames);

        //Setup the "web" environment so we can test.

        $uri = sprintf('/api/foo/baz/?%s=%s',$keyVar, $key);

        $AppEngine->Configs->Server->Request->uri       = $uri;
        $AppEngine->Configs->Server->Request->get_vars  = [$keyVar => $key];
        $AppEngine->Configs->Server->Request->post_vars = [];
        $AppEngine->Configs->Server->Request->cookies   = [];

        //try to authenticate the user and password.
        $result = $AppEngine->runActions('auth-user');

    }

    public function providerTestAPIAuthentication() {
                   //Username                //Password         //authenticates
        return  [ ['key'    , 'cgmckutavuhbdqhmkwgekqhrdxhqyadagqctgwtcq'  , true  ]
                , ['apiKey' , 'cgmckutavuhbdqhmkwgekqhrdxhqyadagqctgwtcq'  , true  ]  //Case insensitive.
                , ['apiKey' , 'cgmckutavuhbdqhmkwgekqhrdxhqyadagqctgwtc'   , false ]  //Too short
                , ['apiKey' , 'cgmckutavuhbdqhmkwgekqhrdxhqyadagqctgwtcx'  , false ]  //Doesn't exist.
                , ['key'    , 'cgmckutavuhbdqhmkwgekqhrdxhqyadagqctgwtc'   , false ]  //Too short
                , ['key'    , 'cgmckutavuhbdqhmkwgekqhrdxhqyadagqctgwtcx'  , false ]  //Doesn't exist.
                ];
    }
}