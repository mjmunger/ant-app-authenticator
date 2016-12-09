<?php

namespace PHPAnt\Authentication;

class AuthorizePageview extends AuthorizationRequest
{
<<<<<<< HEAD
	function enQuote($buffer) {
		return sprintf('"%s"',$buffer);
	}

=======
>>>>>>> 0d68dc256c46056436d0c9ef9352e3f445e0d00f
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
<<<<<<< HEAD
			// echo "<pre>"; var_dump($adSettings); echo "</pre>";
			// die(__FILE__ . ":" . __LINE__);
			if($adSettings && $adSettings['enabled'] == 1) return $this->authenticateADUserPass($args);
=======
			if($adSettings) return $this->authenticateADUserPass($args);
>>>>>>> 0d68dc256c46056436d0c9ef9352e3f445e0d00f

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

		$data     = json_decode( $args['AE']->Configs->getConfigs( [ 'ad-settings' ] )['ad-settings'], true );
		$settings = $data !== null ? $data : [];
		$settings['domain_controllers'] = [$settings['domain_controllers']];

		// 1. Attempt to authenticate against AD
		$adldap = new \Adldap\Adldap($settings);
		$this->authorized = $adldap->user()->authenticate($username, $password);

		$logMessage = ($this->authorized ? "AD authentication successful" : "AD authentication failed");
		$args['AE']->log('Authentication',$logMessage);
		$this->shouldIssueCredentials = $this->authorized;
		$logMessage = ($this->shouldIssueCredentials  ? "Key credentials will be issued." : "Key credentials will not be issued.");
		$args['AE']->log('Authentication',$logMessage);

		// 2. If successful, check to see if the user exists in the DB. (We'll create a user on the fly).

<<<<<<< HEAD
		$sql = "SELECT users_id FROM users where users_guid = ?";
		$stmt = $args['AE']->Configs->pdo->prepare($sql);
		$values = [$guid];
		$result = $stmt->execute($values);

		if($stmt->rowCount() > 0) {
			//return that user's id.
			$row = $stmt->fetchObject();
			return $row->users_id;
		} 

		//First, we have to determine if this user belongs to any groups or not. if they do not belong to any groups in this system, they are denied login access.

		$groups = $adldap->user()->groups($username);
		$info = $adldap->user()->info($username);
		
		//Put quotes around all the groups.
		$groups = array_map([$this,'enQuote'], $groups);
		
		//Create an "IN" clause string
		$inClause = implode(', ', $groups);

		$sql = sprintf("SELECT * FROM users_roles where users_roles_title IN ( %s )",$inClause);
		$stmt = $args['AE']->Configs->pdo->prepare($sql);
		$stmt->execute();

		if($stmt->rowCount() == 0) return false; //User does not belong to any groups that have access to the system.

		//OK, good. The user belongs to a group that has permissions on this system. Now, let's see if the user exists, and if not, create it. 
		$row = $stmt->fetchObject();
		$usersRole = $row->users_roles_id;

		// Get the users information
		echo "<pre>";

		$user = $adldap->user()->info($username);

		$email = $user['mail'];
		$first = $user['givenname'];
		$last  = $user['sn'];
		$guid  = bin2hex($user['objectguid']);

		$sql = "SELECT * FROM users where users_guid = ?";
		$stmt = $args['AE']->Configs->pdo->prepare($sql);
		$values = [$guid];
		$result = $stmt->execute($values);

		//If there is a match, update that user's role and return the use ID.
		if($stmt->rowCount() > 0) {
			$row = $stmt->fetchObject();
			$usersId = $row->users_id;

			//Update the role.
			$sql    = "UPDATE users SET users_roles_id = ?, users_last_login = ? WHERE users_id = ?";
			$update = $args['AE']->Configs->pdo->prepare($sql);
			$update->execute([$usersRole,date('Y-m-d H:i:s'),$usersId]);

			$return = $row->users_id;
			return $return;
		}

		//If we have made it here, the user doesn't exist. We'll have to create it. Since the password and other info is in AD, we really only need to add in the first, last, email, and role.

		$sql = "INSERT INTO `users`
			   ( `users_email`
			   , `users_first`
			   , `users_last`
			   , `users_setup`
			   , `users_active`
			   , `users_owner_id`
			   , `users_roles_id`
			   , `users_guid`
			   )
			   VALUES
			   ( :users_email
			   , :users_first
			   , :users_last
			   , :users_setup
			   , :users_active
			   , :users_owner_id
			   , :users_roles_id
			   , :users_guid
			   )";

		$values = [ 'users_email'      => $email
				  , 'users_first'      => $first
				  , 'users_last'       => $last
				  , 'users_setup'      => 'Y'
				  , 'users_active'     => 'Y'
				  , 'users_owner_id'   => 0
				  , 'users_roles_id'   => $usersRole
				  , 'users_guid'       => $guid
				  ];


		$stmt = $args['AE']->Configs->pdo->prepare($sql);
		$result = $stmt->execute($values);

		$return = $args['AE']->Configs->pdo->lastInsertId();

		return $return;
=======
		

		// 3. If the user exists, GREAT! theyhave logged in before. Update the record if needed.
		// 4. Store any roles / groups in the DB for the user.
		// 5. Return a user ID to make the class upstream happy.

>>>>>>> 0d68dc256c46056436d0c9ef9352e3f445e0d00f
	}
}
