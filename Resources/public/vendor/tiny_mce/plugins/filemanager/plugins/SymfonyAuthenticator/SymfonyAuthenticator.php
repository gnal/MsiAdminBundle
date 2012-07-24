<?php
/**
 * SymfonyAuthenticatorImpl.php
 *
 *
 * -> Assuming credential to upload is called "canuploadassets"
 * -> assuming SF_ROOT_DIR/setup.php contains symfony environment setup, needed to use sfContext
 *
 * @author Ilia Kantor
 */

// assuming standard location for plugin

require_once(dirname(__FILE__).'/../../../../../../../../config/ProjectConfiguration.class.php');

/**
 * This class is a Symfony authenticator implementation.
 *
 * @package MCImageManager.Authenticators
 */
class Moxiecode_SymfonyAuthenticator extends Moxiecode_ManagerPlugin {
    /**#@+
	 * @access public
	 */

	/**
	 * Main constructor.
	 */
	function Moxiecode_SymfonyAuthenticator() {
	}

	function onAuthenticate(&$man) {
   $configuration = ProjectConfiguration::getApplicationConfiguration('backend', 'prod', false);  
   $context = sfContext::createInstance($configuration);  
   return $context->getUser()->isAuthenticated();
	}

	/**#@-*/
}

// Add plugin to MCManager
$man->registerPlugin("SymfonyAuthenticator", new Moxiecode_SymfonyAuthenticator());

