<?php

namespace PHPAnt\Authentication;

class AuthenticationRouter {

	private $authorized;
	private $return;
	private $uri;
	private $AuthenticationWhitelistManager;
	private $AppEngine;
	public  $message = false;

	function __construct($authorized, $return = false, $uri, $AuthenticationWhitelistManager, $AppEngine) {
		$this->authorized 					   = $authorized;
		$this->return     					   = $return;
		$this->uri        					   = $uri;
		$this->AuthenticationWhitelistManager  = $AuthenticationWhitelistManager;
		$this->AppEngine                       = $AppEngine;

	}

	function requireLogin($query) {
	    $params = http_build_query($query);
        $redirect = "/login/?" . $params;

        $this->AppEngine->log( 'AuthenticationRouter'
            , "Request not authorized. Requiring a login. Redirecting to $redirect"
            , 'AppEngine.log'
            , 9
        );

        header("location: " . $redirect);
        return false;
    }

    function disallowReturnToLogin() {
        //We should never be at /login/ after we are authorized. So, if that's where we are, set $this->return to "/" to get us out of here
        $pattern = "'%/login/.*%s'";
        $matches = [];

        preg_match($pattern,$this->uri, $matches);

        if(!$this->return && $this->uri == '/login/') {
            $this->AppEngine->log( 'AuthenticationRouter'
                , "Aborting redirect to /login/ in favor of a redirect to / (hardcoded abort to avoid loops)"
                , 'AppEngine.log'
                , 9
            );
            $this->return = '/';
        }
    }

	function route() {

        //If this URI is whitelisted, authentication is not necessary. Bug out now to prevent 301 redirection loops.
        if($this->AuthenticationWhitelistManager->isWhitelisted($this->uri) && $this->authorized == false) {
            $this->AppEngine->log( 'AuthenticationRouter'
                , "The uri ($this->uri) is whitelisted. Not routing anywhere. Let it ride!"
                , 'AppEngine.log'
                , 9
            );
            return ['success' => true];
        }

	    $query = [];
	    $query[ 'return' ] = ( $this->return  == false ? "/" : $this->return  );
        $query[ 'message'] = ( $this->message == false ? ""  : $this->message );

        //If we are not authorized, show the login page.
        if($this->authorized == false) return $this->requireLogin($query);

        $this->AppEngine->log( 'AuthenticationRouter'
                             , "Routing authorized request"
                             , 'AppEngine.log'
                             , 9
                             );

        $this->disallowReturnToLogin();

        if($this->return == "//") $this->return = "/";

        //If we have a return set in the get request:
        if( $this->return) {
            $this->AppEngine->log( 'AuthenticationRouter'
                                 , "return was set in GET. Redirecting to: " . $this->return
                                 , 'AppEngine.log'
                                 , 9
                                 );
            //header("HTTP/1.1 302 Moved Temporarily");
            header("location: " . $this->return);
        }
        return true;
	}
}
