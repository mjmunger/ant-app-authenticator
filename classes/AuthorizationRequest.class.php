<?php

namespace PHPAnt\Authentication;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

require_once (__DIR__ . '/iAuthorizationRequest.interface.php');

class AuthorizationRequest {

	public $uri                    = NULL;
	public $pdo                    = NULL;
	public $credentials            = NULL;
	public $authorized             = false;
	public $returnURL              = NULL;
	public $cookies                = NULL;
	public $users_id               = false;
	public $users_roles_id         = false;
	public $shouldIssueCredentials = false;
	public $verbosity              = 0;
	public $logMessages            = [];
	public $adSettings			   = false;
	public $authorizationType      = null;
	public $requestType			   = false;
	public $logger                 = false;

	function __construct($options) {
		$this->pdo         = isset($options['pdo'])         ? $options['pdo']                   : NULL  ;
		$this->uri         = isset($options['uri'])         ? $options['uri']                   : NULL  ;
		$this->credentials = isset($options['credentials']) ? $options['credentials']           : NULL  ;
		$this->returnURL   = isset($options['return'])      ? $options['return']                : NULL  ;
		$this->cookies     = isset($options['cookies'])     ? $options['cookies']               : NULL  ;
		$this->verbosity   = isset($options['verbosity'])   ? $options['verbosity']             : 0     ;
		$this->adSettings  = isset($options['ad-settings']) ? $options['ad-settings']           : false ;
		$this->logPath     = isset($options['log-path'])    ? $options['log-path'] . "auth.log" : __DIR__ . '/' . "auth.log" ;

		// create a log channel
        $log = new Logger('Authenticator');
        $log->pushHandler(new StreamHandler($this->logPath, Logger::INFO));

        $this->logger = $log;
	}

	function log($message) {
		array_push($this->logMessages, $message);
	}
}