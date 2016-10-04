<?php

namespace PHPAnt\Authentication;

class AuthenticationRouter {

	private $authorized;
	private $return;
	private $uri;
	private $whitelist;

	function __construct($authorized, $return = false, $uri, $whitelist) {
		$this->authorized = $authorized;
		$this->return     = $return;
		$this->uri        = $uri;
		$this->whitelist  = $whitelist;
		
	}

	function route() {

		//If the URI is in the whitelist, do not process further. This is a logged out page.
		if(in_array($this->uri, $this->whitelist)) return true;

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