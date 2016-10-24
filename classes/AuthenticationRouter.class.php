<?php

namespace PHPAnt\Authentication;

class AuthenticationRouter {

	private $authorized;
	private $return;
	private $uri;
	//private $AuthenticationWhitelistManager;

	function __construct($authorized, $return = false, $uri, $AuthenticationWhitelistManager) {
		$this->authorized 					   = $authorized;
		$this->return     					   = $return;
		$this->uri        					   = $uri;
		$this->AuthenticationWhitelistManager  = $AuthenticationWhitelistManager;
		
	}

	function route() {

		if($this->authorized) {

			//We should never be at /login/ after we are authorized. So, if that's where we are, set $this->return to "/" to get us out of here
			if(!$this->return && $this->uri == '/login/') $this->return = '/';

			//If we have a return set in the get request:
			if($this->return) {
				header("HTTP/1.1 301 Moved Permanently");
				header("location: " . $this->return);
			}
			return true;
		}

        //If this URI is whitelisted, authentication is not necessary. Bug out now to prevent 301 redirection loops.
        if($this->AuthenticationWhitelistManager->isWhitelisted($this->uri)) {
            echo "URI on whitelist. Not authenticating";
            return ['success' => true];
        }

		//If we are not authorized, show the denied page.
		if(!$this->authorized) {
			header("HTTP/1.1 301 Moved Permanently");
			header("location: /login/");
		}

		//Otherwise, let it ride!
	}
}