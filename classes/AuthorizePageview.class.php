<?php

namespace PHPAnt\Authentication;

class AuthorizePageview extends AuthorizationRequest implements iAuthorizationRequest
{

    public function getRequestType() {
        return "AuthorizePageview";
    }

    function enQuote($buffer) {
        return sprintf('"%s"',$buffer);
    }

    function authenticate() {

        $this->logger->info("Attempting key authentication...");
        if($this->authenticateKey())        return ['success' => true];

        //No key? if we are missing something, fail it.
        if(isset($this->credentials['username']) == false) return false;
        if(isset($this->credentials['password']) == false) return false;

        $this->authorizationType = 'user';

        //Authorize with key in cookies if present

        $this->logger->info("Attempting local authentication...");
        if($this->authenticateUserPass())   return ['success' => true];
        $this->logger->info("Attempting AD authentication...");
        if($this->authenticateADUserPass()) return ['success' => true];

        $this->logger->info("Nothing worked. Denying access.");
        //Well, we tried. Nothing worked. Deny access.
        return false;
	}

    function authenticateKey() {
        //If not token, fail.
        if(isset($this->cookies['users_token']) == false) return false;

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
            $this->logger->info('Authentication' . ' ' . "Key not found: " . $this->cookies['users_token']);
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
        $this->logger->info('Authentication'. ' ' .$logMessage);

        return true;
    }

    function getUser($username) {
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

        return $stmt->fetchObject();
    }

    function authenticateUserPass() {

        $this->log("Attempting user / pass authentication");

        if(isset($this->credentials['username']) == false) return false;
        if(isset($this->credentials['password']) == false) return false;

        $username = $this->credentials['username'];
        $password = $this->credentials['password'];
        $hash     = NULL;

        $user = $this->getUser($username);

        if($user == false) return false; //No user. fail.

        $this->authorized = password_verify($password, $user->users_password);

        $logMessage = ($this->authorized ? "Password authentication successful" : "Password authentication failed for " . $username);

        $this->logger->info('Authentication' . ' ' . $logMessage);

        $this->shouldIssueCredentials = $this->authorized;

        $logMessage = ($this->shouldIssueCredentials  ? "Key credentials will be issued." : "Key credentials will not be issued.");

        $this->logger->info('Authentication' . ' ' . $logMessage);

        $this->users_id       = ($this->authorized ? (int) $user->users_id       : false);
        $this->users_roles_id = ($this->authorized ? (int) $user->users_roles_id : false);

        return $this->users_id;
    }

    function authenticateADUserPass() {

        if($this->adSettings == false) return false;

        $adSettings = (count($this->adSettings) > 0 ? json_decode($this->adSettings, true) : false );

        //Quit if AD not enabled.
        if((int) $adSettings['enabled'] != 1) return false;

        $logMessage = "Attempting authentication against active directory";
        $this->logger->info('Authentication' . ' ' . $logMessage);

        // 0. For convenience:
        $username = $this->credentials['username'];
        $password = $this->credentials['password'];
        $hash     = NULL;

        $data     = json_decode( $this->logger->Configs->getConfigs( [ 'ad-settings' ] )['ad-settings'], true );
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

        $this->logger->info("Authentication", "User GUID: " . $guid);

        $this->logger->info('Authentication' . ' ' . sprintf('Active directory authentication returned: %s',($this->authorized ? "Authorized" : "Failed")));

        $logMessage = ($this->authorized ? "AD authentication successful" : "AD authentication failed");
        $this->logger->info('Authentication' . ' ' . $logMessage);
        $this->shouldIssueCredentials = $this->authorized;
        $logMessage = ($this->shouldIssueCredentials  ? "Key credentials will be issued." : "Key credentials will not be issued.");
        $this->logger->info('Authentication' . ' ' . $logMessage);

        // 2. If successful, check to see if the user exists in the DB. (We'll create a user on the fly).

        $sql = "SELECT users_id,users_roles_id FROM users where users_guid = ?";
        $stmt = $this->pdo->prepare($sql);
        $values = [$guid];
        $result = $stmt->execute($values);

        $this->logger->info( 'Authentication'
                , sprintf('User exists in the local database: %s',($stmt->rowCount() > 0 ? "Yes" : "No"))
                        , 'AppEngine.log'
                        , 9
                      );

        if($stmt->rowCount() > 0) {
            //return that user's id.
            $row = $stmt->fetchObject();
            $this->logger->info( 'Authentication'
                    , sprintf('Returning database user ID: %s',  $row->users_id)
                            , 'AppEngine.log'
                            , 9
                          );
            //Set the userId and User Role of this object so that other things don't fail.
            $this->users_id       = $row->users_id;

            //Check the roles to make sure they still have access, or to see if the access should be updated.

            $this->users_roles_id = $row->users_roles_id;
        } else {
            $this->logger->info("Authentication", "GUID did not return any user accounts for the user $username, which authenticated properly. This might be a problem... if the account was changed or the GUID for the user changed, you'll need to update it in the database (here) locally.");
        }

        //First, we have to determine if this user belongs to any groups or not. if they do not belong to any groups in this system, they are denied login access.
        $groups = $adldap->user()->groups($username);

        $groupList = (is_array($groups) ? implode(",", $groups) : "NONE! user should be part of at least one group that has access to this system in order to authenticate and gain access!");


        $this->logger->info( "Authentication"
                             , sprintf("This user is a member of the following groups in AD: %s", $groupList)
                             , 'AppEngine.log'
                             ,10
                             );

        //Put quotes around all the groups.
        $groups = array_map([$this,'enQuote'], $groups);

        //Create an "IN" clause string
        $inClause = implode(', ', $groups);

        $this->logger->info( 'Authentication'
                        , sprintf('User belongs to the following AD groups: %s',  $inClause)
                        , 'AppEngine.log'
                        , 9
                        );

        $sql = sprintf("SELECT * FROM users_roles where users_roles_title IN ( %s )",$inClause);
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        $this->logger->info( 'Authentication'
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
            $this->logger->info( 'Authentication'
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
            //Refresh the user's groups, and / or create them if they do not exist.
            $this->storeUserGroups($row->users_id,$groups);
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
                  , 'users_roles_id'   => 2          //This role should exist by default, and should never have permissions assigned to it by default.
                  , 'users_guid'       => $guid
                  ];


        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute($values);

        $this->users_id = $this->pdo->lastInsertId();
        $this->users_roles_id = $usersRole;

        //Refresh the user's groups, and / or create them if they do not exist.
        $this->storeUserGroups($this->users_id,$groups);

        return $this->users_id;
    }

    public function storeUserGroups($users_id, $groups) {     

        //Delete all groups for this user.
        $sql = "DELETE FROM user_groups 
                WHERE
                    users_users_id = ?";

        $stmt = $this->pdo->prepare($sql);
        $values = [$users_id];
        $result = $stmt->execute($values);

        $this->logger->info( 'AD Auth'
                        , sprintf('Removing all groups for user %s: %s', $users_id, ($result ? "Succcess" : "Failed"))
                        );

        //Add in all the groups we have been passed for this user.
        $this->pdo->beginTransaction();
        $sql = "INSERT INTO `user_groups` (`users_users_id`, `user_groups_group`) VALUES (?, ?)";
        $stmt = $this->pdo->prepare($sql);

        foreach($groups as $group) {
            //Remove quotes
            $group = str_replace('"', '', $group);
            $values = [$users_id, $group];
            $result = $stmt->execute($values);

            $this->logger->info( 'AD Auth'
                                 , sprintf('Adding group (%s) for user id (%s) : %s', $group, $users_id, ($result ? "Succcess" : "Failed"))
                                 );
        }

        $success = $this->pdo->commit();

        $this->logger->info( 'AD Auth'
                                 , sprintf('Groups updated / added for user id %s : ', $users_id, ($success ? "Succcess" : "Failed"))
                                 );
    }
}
