<?php

namespace PHPAnt\Authentication;

class CredentialStorage
{
	//Default to session.
	public $rememberMe = false; //Default to session only.
	public $expiry     = 0;     //Default to session cookies.
	public $pdo        = NULL;
	public $users_id   = NULL;

	function __construct(\PDO $pdo, $users_id) {
		$this->pdo = $pdo;
		$this->users_id = $users_id;
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

		$token = hash_hmac('sha256',$seed, 'Eevohnie0oN2aht');

		return $token;
	}

	function saveUserToken($token, $expiry) {
		$sql    = "INSERT INTO `phpant`.`user_tokens` (`user_tokens_token`, `user_tokens_expiry`, `users_id`) VALUES (?, ?, ?)";
		$stmt   = $this->pdo->prepare($sql);

		//Keep session cookies in the database for 24 hours.
		$expiry = ($expiry == 0 ? 3600 * 24 : $expiry);

		$vars   = [$token
				  , date("Y-m-d H:i:s", time() + $expiry)
				  , $this->users_id
				  ];

		$stmt->execute($vars);

	}

	function issueCredentials() {

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
			     , true                   // Secure only if possible.
			     , true                   // httponly. Deny Javascript access.
			     );
	}
}