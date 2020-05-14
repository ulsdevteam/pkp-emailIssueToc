<?php

/**
 * @filesource plugins/generic/emailIssueToc/emailIssueToc.inc.php
 * 
 * @class emailIssueTocPlugin
 * @ingroup plugins_generic_emailIssueToc
 * 
 * @brief EmailIssueToc plugin class
 * @author suk117
 */

import('lib.pkp.classes.plugins.GenericPlugin');

class emailIssueTocPlugin extends GenericPlugin{

	/**
	 * @copydoc LazyLoadPlugin::register()
	 */
	function register($category, $path, $mainContextId = NULL) {
		$success = parent::register($category, $path, $mainContextId);
		if (!Config::getVar('general', 'installed') || defined('RUNNING_UPGRADE')) return true;
		if ($success && $this->getEnabled()) {
			HookRegistry::register('issuegridhandler::publishissue', array(&$this, 'createNewNotification'));
			HookRegistry::register('notificationmanager::getnotificationmessage', array(&$this, 'sendToc'));
			}
		return $success;
	}

	/**
	 * @copydoc PKPPlugin::getDisplayName()
	*/
	function getDisplayName() {
		return __('plugins.generic.emailIssueToc.displayName');
	}

	/**
	 * @copydoc PKPPlugin::getDescription()
	 */
	function getDescription() {
		return __('plugins.generic.emailIssueToc.description');
	}
	
	/**
	 * Create a new notification using the new notification type
	 * @param string $hookname issuegridhandler::publishissue
	 * @param array $args Description
	 * @return type Description
	 */
	function createNewNotification($hookname, $args) {
		
	}
	
	/**
	 * Add the Table of Content in the email message
	 * @param string $hookname notificationmanager::getnotificationmessage
	 * @param array $args
	 */
	function sendToc($hookname, $args) {
		
	}
}
