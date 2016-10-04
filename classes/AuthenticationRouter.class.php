<?php

namespace PHPAnt\Authentication;

class AuthenticationRouter {

	private $authorized;
	private $return;

	function __construct($authorized, $return = false) {
		$this->authorized = $authorized;
		$this->return     = $return;
		
	}

	function route() {

		//If we are not authorized, show the denied page.
		if(!$this->authorized) {
			header("HTTP/1.1 301 Moved Permanently");
			header("location: /login/");
		}

		//If we have a return set in the get request:
		if($this->return) {
			header("HTTP/1.1 301 Moved Permanently");
			header("location: " . $this->return);
		}

		//Otherwise, let it ride!
	}
}