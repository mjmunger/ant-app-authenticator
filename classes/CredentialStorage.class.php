<?php

namespace PHPAnt\Authentication;

class CredentialStorage
{
	//Default to session.
	public $rememberMe      = false; //Default to session only.
	public $expiry          = 0;     //Default to session cookies.
	public $pdo             = NULL;
	public $users_id        = NULL;
	public $users_roles_id  = NULL;

	function __construct(\PDO $pdo, $users_id, $users_roles_id) {
		$this->pdo 		      = $pdo;
		$this->users_id       = $users_id;
		$this->users_roles_id = $users_roles_id;
	}

	function setRememberMe($rememberMe) {
		$this->rememberMe = $rememberMe;
	}

	function setExpiry($expiry) {

		//We might get a null or "" here if the credentials-vaild-for is not
		//set, so keep it as zero unless we get a number.

		if($expiry > 0) $this->expiry = $expiry;
	}

	function generateToken() {

		if(version_compare(phpversion(), '7.0.0','<')) {
			$seed = bin2hex(openssl_random_pseudo_bytes(64));
		} else {
			$seed = bin2hex(random_bytes(64));
		}

		//To make this better, we should generate this key value per-installation at setup.
		$token = hash_hmac('sha256',$seed, 'Eevohnie0oN2aht');

		return $token;
	}

	function saveUserToken($token, $expiry) {
		$sql    = "INSERT INTO `user_tokens` (`user_tokens_token`, `user_tokens_expiry`, `users_id`, `users_roles_id`) VALUES (?, ?, ?, ?)";
		$stmt   = $this->pdo->prepare($sql);

		//Keep session cookies in the database for 24 hours.
		$expiry = ($expiry == 0 ? 3600 * 24 : $expiry);

		$vars   = [$token
				  , date("Y-m-d H:i:s", time() + $expiry)
				  , $this->users_id
				  , $this->users_roles_id
				  ];

		if(!$stmt->execute($vars)) {
			echo "<pre>"; var_dump($stmt->errorInfo()); echo "</pre>";
			echo "<pre>"; var_dump($stmt); echo "</pre>";
			echo "<pre>"; var_dump($vars); echo "</pre>";
		}
	}

	function issueCredentials($domain) {
		$now    = time();
		$expiry = ($this->rememberMe ? $this->expiry : 0);
		$token  = $this->generateToken();

		//Save the token to the user's account.

		$this->saveUserToken($token,$expiry);
		//Issue the cookie.

		setcookie( 'users_token'          // Cookie name
			     , $token                 // Token - sha256 hash of cryptographically secure random bytes.
			     , $this->expiry + $now   // Cookie will expire now + $this->expiry (in seconds).
			     , '/'                    // Cookie is good for our entire domain / project.
			     , ''                // Entire domain.
			     , true                   // Secure only if possible.
			     //, true                   // httponly. Deny Javascript access.
			     );
	}

    /**
     * Removes the credentials cookie from the browser
     * @param  string   $token    The token that was assigned to this user.
     * @param  string   $domain   The domain for which this credential is good for.
     * @param  boolean $redirect Tells the method whether or not to redirect to the main page after the creds have been removed.
     */
	function removeCredentials($token,$domain, $redirect = true) {

		$sql  = "DELETE FROM `user_tokens` WHERE `user_tokens_token` = ?";
		$stmt = $this->pdo->prepare($sql);
		$vars = [$token];
		if(!$stmt->execute($vars)) {
			echo "<pre>"; var_dump($stmt->errorInfo()); echo "</pre>";
			echo "<pre>"; var_dump($stmt); echo "</pre>";
			echo "<pre>"; var_dump($vars); echo "</pre>";
		}

		//Kill the cookie.
		setcookie( 'users_token'          // Cookie name
			     , ''	                  // Null out the token.
			     , 1					  // 1 is used instead of 0, because 0 sets the cookie to expire at the end of the session.
			     , '/'                    // Cookie is good for our entire domain / project.
			     , ''                     // Entire domain.
			     , true                   // Secure only if possible.
			     //, true                   // httponly. Deny Javascript access.
			     );

		if($redirect) header("location: /");
	}

    function sudo() {
        // Move the current user cookie to the sudo cookie.
        $currentToken = $_COOKIE['users_token'];
        $this->expiry = 3600;

        setcookie( 'sudo'                 // Cookie name
                 , $currentToken          // Null out the token.
                 , 0					  // 1 is used instead of 0, because 0 sets the cookie to expire at the end of the session.
                 , '/'                    // Cookie is good for our entire domain / project.
                 , ''                     // Entire domain.
                 , true                   // Secure only if possible.
                 //, true                   // httponly. Deny Javascript access.
                 );

        // Generate a new token that is associated with this user and save as users_token.
        $this->issueCredentials('/');
    }

    function sudoExit() {
        // Get the sudo token from the cookie.
        $originalToken = $_COOKIE['sudo'];
        $expiry = 3600 * 24 * 30;

        // Remove the sudo cookie
        setcookie( 'sudo'          // Cookie name
			     , ''	                  // Null out the token.
			     , 1					  // 1 is used instead of 0, because 0 sets the cookie to expire at the end of the session.
			     , '/'                    // Cookie is good for our entire domain / project.
			     , ''                     // Entire domain.
			     , true                   // Secure only if possible.
			     //, true                   // httponly. Deny Javascript access.
			     );

        echo "<pre>"; var_dump($originalToken); echo "</pre>";

        $result = setcookie( 'users_token'          // Cookie name
                 , $originalToken         // Null out the token.
                 , 0             	      // 1 is used instead of 0, because 0 sets the cookie to expire at the end of the session.
                 , '/'                    // Cookie is good for our entire domain / project.
                 , ''                     // Entire domain.
                 , true                   // Secure only if possible.
                 //, true                 // httponly. Deny Javascript access.
                 );

    }
}
