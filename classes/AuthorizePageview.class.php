<?php

namespace PHPAnt\Authentication;

class AuthorizePageview extends AuthorizationRequest
{
    function enQuote($buffer) {
        return sprintf('"%s"',$buffer);
    }

    function authenticate() {
        //Authorize with key in cookies if present
        if(isset($this->cookies['users_token'])) return $this->authenticateKey();

        //If there is no token, try to authenticate with user / pass.

        if($this->adSettings != false) $adSettings = (count($this->adSettings) > 0 ? json_decode($this->adSettings, true) : false );

        if(isset($this->credentials['username']) && isset($this->credentials['password'])) {

            //If we are using AD Authentication, check AD, otherwise, check local DB.

            if($this->adSettings) {
                if((int) $adSettings['enabled'] == 1) return $this->authenticateADUserPass();
            }

            //Default to user / pass authentication in our database.
            return $this->authenticateUserPass();
        }

        //Well, we tried.
        return false;
    }

    function authenticateKey() {
        $this->AppEngine->log('Authentication',"Attempting key authentication");

        $sql = "SELECT
                    users_id,users_roles_id
                FROM
                    user_tokens
                WHERE
                    user_tokens_token = ?";

        $stmt = $this->pdo->prepare($sql);
        $vars = [$this->cookies['users_token']];
        $stmt->execute($vars);

        if($stmt->rowCount() === 0) {
            $this->AppEngine->log('Authentication',"Key not found: " . $this->cookies['users_token']);
            return false;
        }

        //Token exists. We are authorized.
        $this->authorized = true;

        $row = $stmt->fetchObject();

        //We authenticated with a valid token, keep using it.
        $this->shouldIssueCredentials = false;

        $this->users_id       = ($this->authorized ? (int) $row->users_id       : false);
        $this->users_roles_id = ($this->authorized ? (int) $row->users_roles_id : false);

        $logMessage = ($this->users_id ? "Key authentication successful" : "Key authentication failed");
        $this->AppEngine->log('Authentication',$logMessage);

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
        $logMessage = ($this->authorized ? "Password authentication successful" : "Password authentication failed for " . $username);
        $this->AppEngine->log('Authentication',$logMessage);

        $this->shouldIssueCredentials = $this->authorized;

        $logMessage = ($this->shouldIssueCredentials  ? "Key credentials will be issued." : "Key credentials will not be issued.");
        $this->AppEngine->log('Authentication',$logMessage);

        $this->users_id       = ($this->authorized ? (int) $row->users_id       : false);
        $this->users_roles_id = ($this->authorized ? (int) $row->users_roles_id : false);

        return $this->users_id;
    }

    function authenticateADUserPass() {

        $logMessage = "Attempting authentication against active directory";
        $this->AppEngine->log('Authentication',$logMessage);

        // 0. For convenience:
        $username = $this->credentials['username'];
        $password = $this->credentials['password'];
        $hash     = NULL;

        $data     = json_decode( $this->AppEngine->Configs->getConfigs( [ 'ad-settings' ] )['ad-settings'], true );
        $settings = $data !== null ? $data : [];
        $settings['domain_controllers'] = [$settings['domain_controllers']];

        // 1. Attempt to authenticate against AD
        $adldap = new \Adldap\Adldap($settings);
        $this->authorized = $adldap->user()->authenticate($username, $password);

        if(!$this->authorized) return false;

        //We are authorized, so let's get the user information from AD.
        $user = $adldap->user()->info($username);

        $email = $user['mail'];
        $first = $user['givenname'];
        $last  = $user['sn'];
        $guid  = bin2hex($user['objectguid']);

        $this->AppEngine->log("Authentication", "User GUID: " . $guid);

        $this->AppEngine->log('Authentication',sprintf('Active directory authentication returned: %s',($this->authorized ? "Authorized" : "Failed")));

        $logMessage = ($this->authorized ? "AD authentication successful" : "AD authentication failed");
        $this->AppEngine->log('Authentication',$logMessage);
        $this->shouldIssueCredentials = $this->authorized;
        $logMessage = ($this->shouldIssueCredentials  ? "Key credentials will be issued." : "Key credentials will not be issued.");
        $this->AppEngine->log('Authentication',$logMessage);

        // 2. If successful, check to see if the user exists in the DB. (We'll create a user on the fly).

        $sql = "SELECT users_id,users_roles_id FROM users where users_guid = ?";
        $stmt = $this->pdo->prepare($sql);
        $values = [$guid];
        $result = $stmt->execute($values);

        $this->AppEngine->log( 'Authentication'
                , sprintf('User exists in the local database: %s',($stmt->rowCount() > 0 ? "Yes" : "No"))
                        , 'AppEngine.log'
                        , 9
                      );

        if($stmt->rowCount() > 0) {
            //return that user's id.
            $row = $stmt->fetchObject();
            $this->AppEngine->log( 'Authentication'
                    , sprintf('Returning database user ID: %s',  $row->users_id)
                            , 'AppEngine.log'
                            , 9
                          );
            //Set the userId and User Role of this object so that other things don't fail.
            $this->users_id       = $row->users_id;

            //Check the roles to make sure they still have access, or to see if the access should be updated.

            $this->users_roles_id = $row->users_roles_id;
        } else {
            $this->AppEngine->log("Authentication", "GUID did not return any user accounts for the user $username, which authenticated properly. This might be a problem... if the account was changed or the GUID for the user changed, you'll need to update it in the database (here) locally.");
        }

        //First, we have to determine if this user belongs to any groups or not. if they do not belong to any groups in this system, they are denied login access.
        $groups = $adldap->user()->groups($username);

        $this->AppEngine->log( "Authentication"
                             , sprintf("This user is a member of the following groups in AD: %s", implode(",", $groups))
                             , 'AppEngine.log'
                             ,10
                             );

        //Put quotes around all the groups.
        $groups = array_map([$this,'enQuote'], $groups);

        //Create an "IN" clause string
        $inClause = implode(', ', $groups);

        $this->AppEngine->log( 'Authentication'
                        , sprintf('User belongs to the following AD groups: %s',  $inClause)
                        , 'AppEngine.log'
                        , 9
                        );

        $sql = sprintf("SELECT * FROM users_roles where users_roles_title IN ( %s )",$inClause);
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        $this->AppEngine->log( 'Authentication'
                        , sprintf('Number of AD groups for this user that have roles in the system: %s',  $stmt->rowCount())
                        , 'AppEngine.log'
                        , 9
                        );

         //User does not belong to any groups that have access to the system.
        if($stmt->rowCount() == 0) {
            //Destroy any hope of getting credentials.
            $this->authorized             = false;
            $this->shouldIssueCredentials = false;
            unset($this->users_id);
            unset($this->users_role_id);
            $this->AppEngine->log( 'Authentication'
                            , sprintf('User %s does not belong to any roles that are installed on the local system. They are only members of: %s',  $username,$inClause)
                            );
            return false;
        }

        //OK, good. The user belongs to a group that has permissions on this system. Now, let's see if the user exists, and if not, create it.
        $row = $stmt->fetchObject();
        $usersRole = $row->users_roles_id;

        // Get the users information
        $sql = "SELECT * FROM users where users_guid = ?";
        $stmt = $this->pdo->prepare($sql);
        $values = [$guid];
        $result = $stmt->execute($values);

        //If there is a match, update that user's role and return the use ID.
        if($stmt->rowCount() > 0) {
            $row = $stmt->fetchObject();
            $usersId = $row->users_id;

            //Update the role.
            $sql    = "UPDATE users SET users_roles_id = ?, users_last_login = ? WHERE users_id = ?";
            $update = $this->pdo->prepare($sql);
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


        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute($values);

        $this->users_id = $this->pdo->lastInsertId();
        $this->users_roles_id = $usersRole;

        return $this->users_id;
    }
}
