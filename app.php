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

    function processCommand($args) {
        $cmd = $args['command'];

        if($cmd->startsWith('authentication uri whitelist')) {
            $this->manageURIWhitelist($args);
        }

        if($cmd->startswith('ad settings')) $this->setAd($args);

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
        $users_id = $AuthorizationRequest->authenticate();


        //Record log messages from the AuthorizationRequest object if verbosity is high enough.
        if($args['AE']->verbosity > 9) {
          foreach($AuthorizationRequest->logMessages as $message) {
              $args['AE']->log('Authenticator',$message);
          }
        }

        //we are going to either create a cookie or kill one. Either way, we need this.
        $CredentialStorage = new \PHPAnt\Authentication\CredentialStorage($args['AE']->Configs->pdo
                                                                         ,$AuthorizationRequest->users_id
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
