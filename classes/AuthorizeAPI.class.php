<?php

namespace PHPAnt\Authentication;

class AuthorizeAPI extends AuthorizationRequest implements iAuthorizationRequest
{
	public function getRequestType() {
		return "AuthorizeAPI";
	}

	/**
	 * Determine if the API key is valid.
	 * Example:
	 *
	 * @return boolean True if key is valid, false otherwise.
	 * @author Michael Munger <michael@highpoweredhelp.com>
	 **/
	function authenticate() {
		
		$sql = "SELECT 
				    api_keys_id
				FROM
				    api_keys
				WHERE
				    api_keys_key = ?
				        AND api_keys_enabled = 'Y'";
		
		$vars = [$this->credentials['key']];
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute($vars);

		$this->authorized = ($stmt->rowCount() > 0);

		return $this->authorized;
	}
}