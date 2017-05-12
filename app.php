<?php

namespace PHPAnt\Core;

/**
 * App Name: PHPAnt Authenticator
 * App Description: Handles basic authentication for PHP-Ant apps.
 * App Version: 1.0
 * App Action: cli-load-grammar -> loadAntAuthenticator       @ 90
 * App Action: cli-init         -> declareMySelf              @ 50
 * App Action: load_loaders     -> AntAuthenticatorAutoLoader @ 50
 * App Action: cli-command      -> processCommand             @ 50
 * App Action: auth-user        -> authenticateUser           @ 50
 */

 /**
 * This app adds the PHPAnt Authenticator and commands into the CLI by adding in
 * the grammar for commands into an array, and returning it up the chain.
 *
 * @package      PHPAnt
 * @subpackage   Core
 * @category     Authentication
 * @author       Michael Munger <michael@highpoweredhelp.com>
 */


class AntAuthenticator extends \PHPAnt\Core\AntApp implements \PHPAnt\Core\AppInterface  {

    /**
     * Instantiates an instance of the AntAuthenticator class.
     * Example:
     *
     * <code>
     * $appAntAuthenticator = new AntAuthenticator();
     * </code>
     *
     * @return void
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/

    function __construct() {
        $this->appName = 'PHPAnt Authenticator';
        $this->canReload = true;
        $this->path = __DIR__;

        //requires to use the CommandList to get grammar... and to avoid crashes.
        $this->AppCommands = new CommandList();
        $this->loadCommands();
    }

    /**
     * Loads CommandInvokers into the app's CommandList so we can execute commands based on them.
     * In order to do this, we need the following things:

     * 1. The callback. This is the callback method of THIS CLASS that will do
     *    the processing. The invoker does not process anything. It simply
     *    decides and delegates processing to a callback within the app.
     *
     *    The callback is required for the CommandInvoker constructor.
     *    $Invoker = new CommandInvoker($callback);
     *
     * 2. The criteria. This is at least one tuple that consists of
     *      a) The method ('is', 'startsWith', 'endsWith', 'contains'), which
     *         is an internal callback to the Command::is, Command::startsWith,
     *         Command::endsWith, and Command:contains() methods.
     *      b) The matching pattern
     *      c) The desired result.
     * 
     * An invoker can have multiple criteria (alhtough one is usually sufficient). Each tuple should be assembled in the following manner:
     * $criteria = [$method => [$pattern => $desiredResult]];
     *
     * Note: You should also make sure your pattern appears in the method of
     * the app that fires when the cli-load-grammar event fires. Future versions of
     * the CommandList class will auto-generate the CLI grammar arrays.      
     **/

    private function loadCommands() {
        

        //Add new users.
        $callback = 'userNew';
        $criteria = ['is' => ['users add' => true]];
        $Invoker = new CommandInvoker($callback);
        $Invoker->addCriteria($criteria);
        $this->AppCommands->add($Invoker);

        //Query / show user.
        $callback = 'userShow';
        $criteria = ['startsWith' => ['users show' => true]];
        $Invoker = new CommandInvoker($callback);
        $Invoker->addCriteria($criteria);
        $this->AppCommands->add($Invoker);

        //Update user password
        $callback = 'userPasswordReset';
        $criteria = ['startsWith' => ['users password reset' => true]];
        $Invoker = new CommandInvoker($callback);
        $Invoker->addCriteria($criteria);
        $this->AppCommands->add($Invoker);

        //Delete user.
        $callback = 'userDelete';
        $criteria = ['startsWith' => ['users delete' => true]];
        $Invoker = new CommandInvoker($callback);
        $Invoker->addCriteria($criteria);
        $this->AppCommands->add($Invoker);

        //Add user roles
        $callback = 'userRolesAdd';
        $criteria = ['startsWith' => ['users roles add' => true]];
        $Invoker = new CommandInvoker($callback);
        $Invoker->addCriteria($criteria);
        $this->AppCommands->add($Invoker);

        //Show user roles
        $callback = 'userRolesShow';
        $criteria = ['startsWith' => ['users roles show' => true]];
        $Invoker = new CommandInvoker($callback);
        $Invoker->addCriteria($criteria);
        $this->AppCommands->add($Invoker);
    }

    /**
     * Callback for the cli-load-grammar action, which adds commands specific to this plugin to the CLI grammar.
     * Example:
     *
     * @return array An array of CLI grammar that will be merged with the rest of the grammar.
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/

    function loadAntAuthenticator() {
        $grammar['authentication'] = [ 'uri' => [ 'whitelist' => [ 'add'    => NULL
                                                                    , 'remove' => NULL
                                                                    , 'show'   => NULL
                                                                    ]
                                                   ]
                                        ];

        $grammar['ad'] = ['settings' => [ 'set'  => NULL
                                        , 'del'  => NULL
                                        , 'show' => NULL
                                        ]
                         ];

        $this->loaded = true;

        //Use the hyrbid approach.
        $grammar = array_merge_recursive($grammar, $this->AppCommands->getGrammar());
        $results['grammar'] = $grammar;
        $results['success'] = true;
        return $results;
    }

    //Uncomment this function and the following function to enable the autoloader for this plugin.
    function AntAuthenticatorAutoLoader() {
        //REGISTER THE AUTOLOADER! This has to be done first thing!
        spl_autoload_register(array($this,'loadAntAuthenticatorClasses'));
        return ['success' => true];

    }

    public function loadAntAuthenticatorClasses($class) {

        $buffer = explode('\\', $class);
        $class = end($buffer);

        $baseDir = $this->path;

        $candidate_files = array();

        //Try to grab it from the classes directory.
        $candidate_path = sprintf($baseDir. '/classes/%s.class.php',$class);
        array_push($candidate_files, $candidate_path);

        //Loop through all candidate files, and attempt to load them all in the correct order (FIFO)
        foreach($candidate_files as $dependency) {
            if($this->verbosity > 11) printf("Looking to load %s",$dependency) . PHP_EOL;
            // printf("Looking to load %s <br>",$dependency) . PHP_EOL;

            if(file_exists($dependency)) {
                if(is_readable($dependency)) {

                    //Print debug info if verbosity is greater than 9
                    if($this->verbosity > 11) print "Including: " . $dependency . PHP_EOL;

                    //Include the file!
                    require_once($dependency);
                    // print "Found: " . $dependency;
                    // print "<BR>";
                }
            }
        }
        return ['success' => true];
    }

    /**
     * Callback function that prints to the CLI during cli-init to show this plugin has loaded.
     * Example:
     *
     * @return array An associative array declaring the status / success of the operation.
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/

    function declareMySelf() {
        if($this->verbosity > 4 && $this->loaded ) print("PHPAnt Authenticator app loaded.\n");

        return ['success' => true];
    }

    function manageURIWhitelist($args) {

        $AWM = new AuthenticationWhitelistManager($args);

        switch ($args['command']->tokens[3]) {

            case 'add':
                $regex = $args['command']->leftStrip('authentication uri whitelist add');
                print "Adding $regex to URI Whitelist Registry" . PHP_EOL;
                $AWM->add($regex);
                break;

            case 'remove':
                $regex = $args['command']->leftStrip('authentication uri whitelist remove');
                $AWM->remove($regex);
                break;

            case 'show':
                $AWM->show();
                break;

            default:
                print "Command not understood." . PHP_EOL;
                break;
        }

    }

    function userRolesAdd($args) {
        $cmd = $args['command'];
        $pdo = $args['AE']->Configs->pdo;

        $newRole = $cmd->leftStrip('users roles add');
        $UsersRoles = new UsersRoles($pdo);
        $UsersRoles->users_roles_title = $newRole;
        $UsersRoles->generateAbbreviation();
        $UsersRoles->insert_me();

        if($UsersRoles->threw_db_error()){
            echo "Error adding user role!" . PHP_EOL;
            $UsersRoles->getDBError();
        }

        echo "User role added successfully." . PHP_EOL;
        return ['success' => true];
    }

    function userRolesShow($args) {
        $cmd = $args['command'];
        $pdo = $args['AE']->Configs->pdo;

        $sql = 'SELECT users_roles_id, users_roles_title, users_roles_role FROM users_roles;';

        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute();

        $map = [];

        $TL = new TableLog();
        $TL->setHeader(['ID', 'Role']);
        while($row = $stmt->fetchObject()) {
            $map[$row->users_roles_role] = $row->users_roles_id;

            $buffer = [ $row->users_roles_role
                      , $row->users_roles_title
                      ];
            $TL->addRow($buffer);
        }

        $TL->showTable();

        return $map;
    }

    function userShow($args) {
        $cmd = $args['command'];
        $pdo = $args['AE']->Configs->pdo;
        $idList = [];

        $sql = 'SELECT 
    users_id, users_email, users_first, users_last, users_roles_title
FROM
    users
        LEFT JOIN
    users_roles ON users_roles.users_roles_id = users.users_roles_id';

        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute();

        $TL = new TableLog();
        $TL->setHeader(['ID', 'Username', 'First', 'Last', 'Role']);
        while($row = $stmt->fetchObject()) {
            array_push($idList,(int) $row->users_id);
            $buffer = [ $row->users_id
                      , $row->users_email
                      , $row->users_first
                      , $row->users_last
                      , $row->users_roles_title
                      ];
            $TL->addRow($buffer);
        }

        $TL->showTable();

        return $idList;
    }

    private function getNewPassword() {
        //Seed this value.
        $confirm = "!";

        while(strcmp($pass, $confirm) !== 0) {            

            echo "Set a password for this user. (Leave blank for a random password to be generated)" . PHP_EOL;
            $pass = trim(fgets(STDIN));

            switch(strlen($pass) > 0) {
                case true:
                    echo "Confirm the password for this user. (Leave blank for a random password to be generated)" . PHP_EOL;
                    $confirm = trim(fgets(STDIN));
                    if(strcmp($pass,$confirm) !== 0) echo "Passwords do not match. Try again." . PHP_EOL;
                    break;
                default:
                    $allowedChars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                    $dictionary = str_split($allowedChars);
                    $pass = '';
                    $ubound = count($dictionary);
                    for($x = 0; $x< 16; $x++) {
                        $pointer = random_int(0,$ubound);
                        $pass .= $dictionary[$pointer];
                    }
                    echo "Using $pass for this user's password. WRITE THIS DOWN now, it is not saved ANYWHERE in plaintext." . PHP_EOL;

                    //Make sure the loop ends.
                    $confirm = $pass;
                    break;
            }
        }

        return $pass;
    }

    function userNew($args) {
        $cmd = $args['command'];
        $pdo = $args['AE']->Configs->pdo;

        echo "Creating a new user:" . PHP_EOL;
        echo PHP_EOL;

        echo "Enter user's first name:" . PHP_EOL;
        $first = trim(fgets(STDIN));

        echo "Enter user's last name:" . PHP_EOL;
        $last = trim(fgets(STDIN));

        echo "Give the user a username (email address suggested)" . PHP_EOL;
        $user = trim(fgets(STDIN));

        $pass = $this->getNewPassword();

        $map = $this->userRolesShow($args);

        echo "Select what role this user shall have." . PHP_EOL;
        $role = trim(fgets(STDIN));

        $roleId = $map[$role];

        echo "Creating new user!" . PHP_EOL;

        $User = new Users($pdo);
        $User->users_email    = $user;
        $User->users_password = password_hash($pass,PASSWORD_DEFAULT);
        $User->users_first    = $first;
        $User->users_last     = $last;
        $User->users_roles_id = $roleId;
        $User->users_active   = 'Y';
        $id = $User->insert_me();

        if($User->threw_db_error()) var_dump($User->pdo->errorInfo());

        if($id) echo "User created successfully." . PHP_EOL;

    }

    private function selectUserId($idList, $prompt) {

        $valid = false;

        while($valid == false) {
            printf("%s (Enter . to escape)" . PHP_EOL, $prompt);
            $id = trim(fgets(STDIN));

            //Escape with '.'
            if($id == '.') return ['success' => false];

            //Make sure it's a number.
            if(!is_numeric($id)) {
                echo "Select a number, please." . PHP_EOL;
                continue;
            }

            $id = (int) $id;

            if(!in_array($id, $idList)) {
                echo "Invalid choice. Please select a number from the user list above." . PHP_EOL;
                continue;
            }

            $valid = true;
        }

        return $id;
    }
    function userPasswordReset($args) {
        $cmd = $args['command'];
        $pdo = $args['AE']->Configs->pdo;
        $idList = $this->userShow($args);

        $id = $this->selectUserId($idList, "Which user should have their password reset?");

        $pass = $this->getNewPassword();

        $User = new Users($pdo);
        $User->users_id = $id;
        $User->load_me();

        $User->users_password = password_hash($pass, PASSWORD_DEFAULT);
        $result = $User->update_me();

        if($result == false) {
            echo "Could not update password." . PHP_EOL;
        } 

        echo "Password updated successfully." . PHP_EOL;

    }

    function userDelete($args) {
        $cmd = $args['command'];
        $pdo = $args['AE']->Configs->pdo;
        $idList = $this->userShow($args);

        $id = $this->selectUserId($idList, "Which user should we delete?");

        echo "You are about to delete the following user: " . PHP_EOL;

        $sql = ' SELECT 
                     users_id,
                     users_email,
                     users_first,
                     users_last,
                     users_roles_title
                 FROM
                     users
                         LEFT JOIN
                     users_roles ON users_roles.users_roles_id = users.users_roles_id
                 WHERE
                    users_id = ?';

        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([$id]);
        if(!$result) var_dump($stmt->errorInfo());
        $row = $stmt->fetchObject();

        $buffer = [ $row->users_id
                  , $row->users_email
                  , $row->users_first
                  , $row->users_last
                  , $row->users_roles_title
                  ];

        $TL = new TableLog();
        $TL->setHeader(['ID', 'Username', 'First', 'Last', 'Role']);
        $TL->addRow($buffer);
        $TL->showTable();
        echo PHP_EOL;
        ECHO "WARNING: THIS CANNOT BE UNDONE AND MAY CAUSE A CASCADE OF DELETIONS OF DATA ASSOCAITED WITH THIS USER!";
        echo PHP_EOL;
        echo PHP_EOL;
        echo "Type DELETE to confirm the deletion of this user." . PHP_EOL;
        $confirm = trim(fgets(STDIN));

        if(strcmp($confirm,'DELETE') !== 0) {
            echo "You must confirm by typing DELETE in the confirmation dialog above. Aborting user delete." . PHP_EOL;
        }

        $sql = 'DELETE FROM users WHERE users_id = ? LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([$id]);
        
        if(!$result) {
            echo "Error deleting user!" . PHP_EOL;
            var_dump($stmt->errorInfo());
            return ['success' => false];
        }

        echo "User successfully deleted." . PHP_EOL;

        return ['success' => true];

    }

    function processCommand($args) {
        $cmd = $args['command'];

        if($cmd->startsWith('authentication uri whitelist')) {
            $this->manageURIWhitelist($args);
        }

        if($cmd->startswith('ad settings')) $this->setAd($args);

        //Use the AppCommands to process the command.
        foreach($this->AppCommands as $Invoker) {
            $callback = $Invoker->callback;
            if($Invoker->shouldRunOn($cmd)) $this->$callback($args);
        }

        return ['success' => true];
    }

    function setAd($args) {
        $settings = $args['command']->leftStrip('ad settings');
        switch($args['command']->getToken(2)) {
            case 'set':
                $buffer = explode(' ', $settings);
                $data = json_decode($args['AE']->Configs->getConfigs(['ad-settings'])['ad-settings'], true);
                $settings = $data !== null ? $data : [];
                $settings[$buffer[1]] = $buffer[2];
                $save = json_encode($settings);
                $args['AE']->Configs->setConfig('ad-settings', $save);
                break;
            case 'del':
                $key = $args['command']->getToken(3);
                if(!empty($key)) {
                    $data     = json_decode( $args['AE']->Configs->getConfigs( [ 'ad-settings' ] )['ad-settings'], true );
                    $settings = $data !== null ? $data : [];
                    unset($settings[$key]);
                    $save = json_encode($settings);
                    $args['AE']->Configs->setConfig('ad-settings', $save);
                }
                break;
            case 'show':
                $data = json_decode($args['AE']->Configs->getConfigs(['ad-settings'])['ad-settings'], true);
                foreach($data as $key => $value) {
                    print str_pad($key,20);
                    print $value;
                    print PHP_EOL;
                }
                break;
            default:
                echo "Command not understood.\n";
                break;
        }
    }

    function authenticateUser($args) {

        //Allow CLI access all the time.
        if(php_sapi_name() == 'cli') return ['success' => true] ;

        //Pass down app verbosity for debugging.
        $options['verbosity']   = $this->verbosity;

        //Get the authorization request object:
        $options['AppEngine']   = $args['AE'];
        $options['pdo']         = $args['AE']->Configs->pdo;
        $options['uri']         = $args['AE']->Configs->Server->Request->uri;
        $options['get']         = $args['AE']->Configs->Server->Request->get_vars;
        $options['post']        = $args['AE']->Configs->Server->Request->post_vars;
        $options['cookies']     = $args['AE']->Configs->Server->Request->cookies;
        $options['ad-settings'] = ( isset($args['AE']->Configs->getConfigs(['ad-settings'])['ad-settings'])
                                  ? $args['AE']->Configs->getConfigs(['ad-settings'])['ad-settings']
                                  : false
                                  ) ;

        //Default the return to the main page...
        $options['return']      = false;

        //Allow it to be overridden if return is set in the GET request.
        if(isset($args['AE']->Configs->Server->Request->get_vars['return'])) $options['return'] = $args['AE']->Configs->Server->Request->get_vars['return'];

        $options['credentials'] = array_merge($options['get'], $options['post']);

        //If we are using API authentication, the key should be in the get request.
        //If we are using content authentication, the user / pass should be in post vars.

        $AuthorizationRequest = \PHPAnt\Authentication\RequestFactory::getRequestAuthorization($options);

        $args['AE']->log( $this->appName
                        , 'Authorization type: ' . $AuthorizationRequest->getRequestType()
                        , 'AppEngine.log'
                        , 9
                        );
        
        $users_id = $AuthorizationRequest->authenticate();


        //Record log messages from the AuthorizationRequest object if verbosity is high enough.
        if($args['AE']->verbosity > 9) {
          foreach($AuthorizationRequest->logMessages as $message) {
              $args['AE']->log('Authenticator',$message);
          }
        }

        //we are going to either create a cookie or kill one. Either way, we need this.
        $CredentialStorage = new \PHPAnt\Authentication\CredentialStorage($args['AE']->Configs->pdo
                                                                         ,$users_id
                                                                         ,$AuthorizationRequest->users_roles_id
                                                                         );

        //Is we are authorized (by user / pass) and should issue an authorization token (cookie), then...
        if($AuthorizationRequest->authorized && $AuthorizationRequest->shouldIssueCredentials) {

            //Store the credentials for the session or for a while.
            $CredentialStorage->setRememberMe(isset($args['AE']->Configs->Server->Request->post_vars['remember']));
            $configs = $args['AE']->Configs->getConfigs(['credentials-valid-for']);

            $expiry = (count($configs) > 0 ? $configs['credentials-valid-for'] : 1800 );

            $CredentialStorage->setExpiry($expiry);
            $CredentialStorage->issueCredentials($args['AE']->Configs->getDomain());
        }

        //Save the current_user in the AppEngine for later use.

        //This needs to be refactored. Nested if's are to solve a problem. API doesn't need cookie authorization, and throws a ton of errors.
        if($AuthorizationRequest instanceof PHPAnt\Authentication\AuthorizePageview ) {
            if($AuthorizationRequest->authorized) {
                $current_user = new Users($args['AE']->Configs->pdo);
                $current_user->users_id = $users_id;
                $current_user->load_me();

                $return['current_user'] = $current_user;

                if(!is_null($current_user)) $args['AE']->log($current_user->getFullName(),"Accessed: " . $args['AE']->Configs->Server->Request->uri);
            } else {
                //Destory the cookie (if it exists) because it was not valid.
                $domain = $args['AE']->Configs->getDomain();

                $token  = ( isset($args['AE']->Configs->Server->Request->cookies['users_token'])
                          ? $args['AE']->Configs->Server->Request->cookies['users_token']
                          : false
                          );

                if($token) $CredentialStorage->removeCredentials($token,$domain);
            }
        }

        if($AuthorizationRequest instanceof PHPAnt\Authentication\AuthorizeAPI) {
            $args['AE']->log("API Accessed: " . $args['AE']->Configs->Server->Request->uri);
        }

        $AuthenticationWhitelistManager = new AuthenticationWhitelistManager($args);

        $AuthorizationRouter = new \PHPAnt\Authentication\AuthenticationRouter( $AuthorizationRequest->authorized          // Submit the state of authorization.
                                                                              , $options['return']                         // If a return url is specified, submit that.
                                                                              , $args['AE']->Configs->Server->Request->uri // Give the full URI so we can compare it to the whitelist of non-authenticated urls.
                                                                              , $AuthenticationWhitelistManager            // Allows us to handle whitelisted URIs.
                                                                              );

        $AuthorizationRouter->route();

        $return['success']      = $AuthorizationRequest->authorized;

        return $return;
    }
}
