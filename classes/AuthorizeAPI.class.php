<?php

namespace PHPAnt\Authentication;

class AuthorizeAPI extends AuthorizationRequest implements iAuthorizationRequest
{
	public $keyExists   = false;
	public $keyEnabled  = false;

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

	    $this->authorizationType = 'api';

		$sql = "SELECT 
				    api_keys_id, api_keys_enabled
				FROM
				    api_keys
				WHERE
				    api_keys_key = ?";
		
		//Default: use the simpler "key"
		if(isset($this->credentials['key'])) $vars = [$this->credentials['key']];

		//Legacy support for apiKey - override if this variable is used. This should be removed in future releases.
		if(isset($this->credentials['apiKey'])) $vars = [$this->credentials['apiKey']];

		$stmt = $this->pdo->prepare($sql);
		$stmt->execute($vars);

		$this->keyExists = ($stmt->rowCount() > 0);

		//If the key doesn't exist, return false.
		if( $this->keyExists == false ) return $this->authorized;

		$row = $stmt->fetchObject();

		$this->keyEnabled = ($row->api_keys_enabled == 'Y' ? true : false);

		//If key not enabled, return false (default = not authorized)
		if($this->keyEnabled == false) return $this->authorized;

		//Lastly, authorize this if the key exists and is enabled.
		$this->authorized = ($this->keyExists && $this->keyEnabled);
		return $this->authorized;
	}
}