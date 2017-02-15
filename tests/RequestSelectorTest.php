
<?php

use PHPUnit\Framework\TestCase;

$dependencies = [ 'test_top.php'
				, 'includes/apps/ant-app-authenticator/classes/AuthorizationRequest.class.php'
				, 'includes/apps/ant-app-authenticator/classes/AuthorizeAPI.class.php'
				, 'includes/apps/ant-app-authenticator/classes/AuthorizePageview.php'
				, 'includes/apps/ant-app-authenticator/classes/RequestFactory.class.php'
				];

foreach($dependencies as $dependency) {
	if(file_exists($dependency)) require_once($dependency);
}

class AuthorizationRequestTest extends TestCase
{

	/**
	 * Determines if we get the correct type of request authorization object.
	 *
	 * @dataProvider providerTestURLs
	 * @covers PageviewOrAPI
	 * @return void
	 */

	public function testPageviewOrAPI($uri,$expectedClass, $credentials) {
		$pdo = new PDOMock();

		$options['credentials'] = $credentials;
		$options['uri']         = $uri;
		$options['pdo']         = $pdo;

		$AuthorizationRequest = PHPAnt\Authentication\RequestFactory::getRequestAuthorization($options);
		$this->assertInstanceOf($expectedClass, $AuthorizationRequest);

		$this->assertInstanceOf('\PDO', $AuthorizationRequest->pdo);
	}
	
	/**
	 * Data Provider for testPageviewOrAPI
	 *
	 * @return array
	 */

	public function providerTestURLs() {
	    return array( ['/api/pos/v1/ticket/'    , 'PHPAnt\Authentication\AuthorizeAPI'      , ['key' => '123456789']]
	                , ['/api/pos/v1/ticket/1234', 'PHPAnt\Authentication\AuthorizeAPI'      , ['key' => '123456789']]
	                , ['/ticket/'               , 'PHPAnt\Authentication\AuthorizePageview' , ['username' => 'foouser', 'password', 'foopass']]
	                , ['/ticket/1234'           , 'PHPAnt\Authentication\AuthorizePageview' , ['username' => 'foouser', 'password', 'foopass']]
	    			);
	}
	

}