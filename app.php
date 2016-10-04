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
    }

    /**
     * Callback for the cli-load-grammar action, which adds commands specific to this plugin to the CLI grammar.
     * Example:
     *
     * @return array An array of CLI grammar that will be merged with the rest of the grammar. 
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/

    function loadAntAuthenticator() {
        $grammar = [];

        $this->loaded = true;
        
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
        $candidate_path = sprintf($baseDir.'/classes/%s.class.php',$class);
        array_push($candidate_files, $candidate_path);

        //Loop through all candidate files, and attempt to load them all in the correct order (FIFO)
        foreach($candidate_files as $dependency) {
            if($this->verbosity > 14) printf("Looking to load %s",$dependency) . PHP_EOL;
            //printf("Looking to load %s",$dependency) . PHP_EOL;

            if(file_exists($dependency)) {
                if(is_readable($dependency)) {

                    //Print debug info if verbosity is greater than 9
                    if($this->verbosity > 9) print "Including: " . $dependency . PHP_EOL;

                    //Include the file!
                    include($dependency);
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

    function processCommand($args) {
        $cmd = $args['command'];

        return ['success' => true];
    }

    function authenticateUser($args) {

        //Allow CLI access all the time.
        if(php_sapi_name() == 'cli') return ['success' => true] ;
        //Get the authorization request object:
        $options['pdo']         = $args['AE']->Configs->pdo;
        $options['uri']         = $args['AE']->Configs->Server->Request->uri;
        $options['get']         = $args['AE']->Configs->Server->Request->get_vars;
        $options['post']        = $args['AE']->Configs->Server->Request->post_vars;
        $options['cookies']     = $args['AE']->Configs->Server->Request->cookies;

        $options['return']      = false;
        if(isset($args['AE']->Configs->Server->Request->get_vars['return'])) $options['return'] = $args['AE']->Configs->Server->Request->get_vars['return'];

        $options['credentials'] = array_merge($options['get'], $options['post']);

 
        //If we are using API authentication, the key should be in the get request.
        //If we are using content authentication, the user / pass should be in post vars.

        $AuthorizationRequest = \PHPAnt\Authentication\RequestFactory::getRequestAuthorization($options);
        $AuthorizationRequest->authenticate($options);

        //Is we are authorized (by user / pass) and should issue an authorization token (cookie), then...
        if($AuthorizationRequest->authorized && $AuthorizationRequest->shouldIssueCredentials) {
            //Store the credentials for the session or for a while.
            $CredentialStorage = new \PHPAnt\Authentication\CredentialStorage();
            $CredentialStorage->setRememberMe(isset($args['AE']->Configs->Server->Request->post_vars['remember']));
            $CredentialStorage->setExpiry($args['AE']->Configs->getConfigs(['credentials-valid-for']));
            $CredentialStorage->issueCredentials();
        }

        //Save the current_user in the AppEngine for later use.
        
        if($AuthorizationRequest->authorized) {
            $current_user = new Users($args['AE']->Configs->pdo);
            $current_user->users_id = $AuthorizationRequest->users_id;
            $current_user->load_me();
    
            $return['current_user'] = $current_user;

            if(!is_null($current_user)) $args['AE']->log($current_user->getFullName(),"Accessed: " . $args['AE']->Configs->Server->Request->uri);
        }

        $AuthorizationRouter = new \PHPAnt\Authentication\AuthenticationRouter( $AuthorizationRequest->authorized          // Submit the state of authorization.
                                                                              , $options['return']                         // If a return url is specified, submit that.
                                                                              , $args['AE']->Configs->Server->Request->uri // Give the full URI so we can compare it to the whitelist of non-authenticated urls.
                                                                              , ['/login/']                                // An array of urls (URI's) that do not require authentication. Like /login/
                                                                              );
        $AuthorizationRouter->route();

        $return['success']      = $AuthorizationRequest->authorized;

        return $return;
    }
}