<?php

namespace PHPAnt\Authentication;

class AuthorizeADPageview extends AuthorizePageView 
{

	function authenticate($options, $args) {
		//Authorize with key in cookies if present
		if(isset($this->cookies['users_token'])) return $this->authenticateKey();

		//If there is no token, try to authenticate with user / pass.
		if(isset($this->credentials['username']) && isset($this->credentials['password'])) return $this->authenticateUserPass($args);

		//Well, we tried.
		return false;
	}
/*
	function authenticateKey() {
		$this->log("Attempting key authentication");

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

		$logMessage = ($this->users_id ? "Key authentication successful" : "Key authentication failed");
		$this->log($logMessage);

		return $this->users_id;
	}
*/
	function authenticateUserPass($args) {

		$this->log("Attempting user / pass AD authentication");

		$username = $this->credentials['username'];
		$password = $this->credentials['password'];
		$hash     = NULL;
/*
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
*/
		//$this->authorized = password_verify($password, $row->users_password);
		$settings = json_decode($args['AE']->Configs->getConfigs(['ad-settings'])['ad-settings'], true);
		$settings['domain_controllers'] = [$settings['domain_controllers']];
		$adldap = new adLDAP($settings);
		$this->authorized = $adldap->user()->authenticate($username, $password);

		$logMessage = ($this->authorized ? "AD authentication successful" : "AD authentication failed");
		$this->log($logMessage);

		$this->shouldIssueCredentials = $this->authorized;

		$logMessage = ($this->shouldIssueCredentials  ? "Key credentials will be issued." : "Key credentials will not be issued.");
		$this->log($logMessage);

		$this->users_id       = ($this->authorized ? (int) 1/*$row->users_id*/       : false);
		$this->users_roles_id = ($this->authorized ? (int) 1/*$row->users_roles_id*/ : false);

		return $this->users_id;
	}
}
