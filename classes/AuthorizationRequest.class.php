<?php

namespace PHPAnt\Authentication;

class AuthorizationRequest {

	public $uri                    = NULL;
	public $AppEngine              = NULL;
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

	function __construct($options) {
		$this->AppEngine   = isset($options['AppEngine'])   ? $options['AppEngine']   : NULL  ;
		$this->pdo         = isset($options['pdo'])         ? $options['pdo']         : NULL  ;
		$this->uri         = isset($options['uri'])         ? $options['uri']         : NULL  ;
		$this->credentials = isset($options['credentials']) ? $options['credentials'] : NULL  ;
		$this->returnURL   = isset($options['return'])      ? $options['return']      : NULL  ;
		$this->cookies     = isset($options['cookies'])     ? $options['cookies']     : NULL  ;
		$this->verbosity   = isset($options['verbosity'])   ? $options['verbosity']   : 0     ;
		$this->adSettings  = isset($options['ad-settings']) ? $options['ad-settings'] : false ;
	}

	function log($message) {
		array_push($this->logMessages, $message);
	}
	
}