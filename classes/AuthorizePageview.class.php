<?php

namespace PHPAnt\Authentication;

class AuthorizePageview extends AuthorizationRequest 
{
	function authenticate() {
		//Authorize with key in cookies if present
		if(isset($this->cookies['users_token'])) return $this->authenticateKey();

		//If there is no token, try to authenticate with user / pass.
		if(isset($this->credentials['username']) && isset($this->credentials['password'])) return $this->authenticateUserPass();

		//Well, we tried.
		return false;
	}

	function authenticateKey() {
		$sql = "SELECT 
				    users_id
				FROM
				    user_tokens
				WHERE
				    user_tokens_token = ?";

		$stmt = $this->pdo->prepare($sql);
		$vars = [$this->cookies['users_token']];
		$stmt->execute($vars);

		if($stmt->rowCount() === 0) return false;		

		//Token exists. We are authorized.
		$this->authorized = true;

		$row = $stmt->fetchObject();

		//We authenticated with a valid token, keep using it.
		$this->shouldIssueCredentials = false;

		$this->users_id = ($this->authorized ? (int) $row->users_id : false);

		return $this->users_id;
	}

	function authenticateUserPass() {

		$username = $this->credentials['username'];
		$password = $this->credentials['password'];
		$hash     = NULL;

		//First, find the user account, if it exists.
		$sql = "SELECT 
				    users_id, users_roles_id, users_password
				FROM
				    users
				WHERE
				    users_email = ? LIMIT 1";

		$stmt = $this->pdo->prepare($sql);
		$vars = [$username];
		$stmt->execute($vars);

		if($stmt->rowCount() === 0) return false;

		$row = $stmt->fetchObject();

		$this->authorized = password_verify($password, $row->users_password);
		
		$this->shouldIssueCredentials = $this->authorized;

		$this->users_id       = ($this->authorized ? (int) $row->users_id       : false);
		$this->users_roles_id = ($this->authorized ? (int) $row->users_roles_id : false);

		return $this->users_id;
	}
}