<?php

namespace PHPAnt\Authentication;

class AuthorizePageview extends AuthorizationRequest
{
	function authenticate($options, $args) {

		//Authorize with key in cookies if present
		if(isset($this->cookies['users_token'])) return $this->authenticateKey($args);

		//If there is no token, try to authenticate with user / pass.

		// Check to see if you are using Active Directory Authentication
		$adSettings = $args['AE']->Configs->getConfigs(['ad-settings'])['ad-settings'];
		// If the array has content, try to convert to a JSON object.
		$adSettings = (count($adSettings) > 0 ? json_decode($adSettings, true) : false );

		if(isset($this->credentials['username']) && isset($this->credentials['password'])) {
			//If we are using AD Authentication, check AD, otherwise, check local DB.
			if($adSettings) return $this->authenticateADUserPass($args);

			//Default to user / pass authentication in our database.
			return $this->authenticateUserPass($args);
		}

		//Well, we tried.
		return false;
	}

	function authenticateKey($args) {
		$args['AE']->log('Authentication',"Attempting key authentication");

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
		$args['AE']->log('Authentication',$logMessage);

		return $this->users_id;
	}

	function authenticateUserPass() {

		$this->log("Attempting user / pass authentication");

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
		$logMessage = ($this->authorized ? "Password authentication successful" : "Password authentication failed");
		$args['AE']->log('Authentication',$logMessage);

		$this->shouldIssueCredentials = $this->authorized;

		$logMessage = ($this->shouldIssueCredentials  ? "Key credentials will be issued." : "Key credentials will not be issued.");
		$args['AE']->log('Authentication',$logMessage);

		$this->users_id       = ($this->authorized ? (int) $row->users_id       : false);
		$this->users_roles_id = ($this->authorized ? (int) $row->users_roles_id : false);

		return $this->users_id;
	}

	function authenticateADUserPass($args) {

		$logMessage = "Attempting authentication against active directory";
		$args['AE']->log('Authentication',$logMessage);

		// 0. For convenience:
		$username = $this->credentials['username'];
		$password = $this->credentials['password'];
		$hash     = NULL;

		// 1. Attempt to authenticate against AD
		$adldap = new \AdLDAP\AdLDAP($settings);
		$this->authorized = $adldap->user()->authenticate($username, $password);

		$logMessage = ($this->authorized ? "AD authentication successful" : "AD authentication failed");
		$args['AE']->log('Authentication',$logMessage);
		$this->shouldIssueCredentials = $this->authorized;
		$logMessage = ($this->shouldIssueCredentials  ? "Key credentials will be issued." : "Key credentials will not be issued.");
		$args['AE']->log('Authentication',$logMessage);

		// 2. If successful, check to see if hte user exists in the DB. (We'll create a user on the fly).
		// 3. If the user exists, GREAT! theyhave logged in before. Update the record if needed.
		// 4. Store any roles / groups in the DB for the user.
		// 5. Return a user ID to make the class upstream happy.

	}
}
