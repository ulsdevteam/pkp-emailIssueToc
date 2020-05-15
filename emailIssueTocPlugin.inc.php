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
$bit = NOTIFICATION_TYPE_PUBLISHED_ISSUE | NOTIFICATION_TYPE_PLUGIN_BASE;
define('NOTIFICATION_TYPE_PUBLISHED_ISSUE_WITH_TOC', $bit);

class emailIssueTocPlugin extends GenericPlugin{

	/**
	 * @copydoc LazyLoadPlugin::register()
	 */
	function register($category, $path, $mainContextId = NULL) {
		$success = parent::register($category, $path, $mainContextId);
		if (!Config::getVar('general', 'installed') || defined('RUNNING_UPGRADE')) return true;
		if ($success && $this->getEnabled()) {
			HookRegistry::register('IssueGridHandler::publishIssue', array(&$this, 'createNewNotification'));
			HookRegistry::register('NotificationManager::getNotificationMessage', array(&$this, 'sendToc'));
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
	 * @return boolean False to continue execution
	 */
	function createNewNotification($hookname, $args) {
		$request = $this->getRequest();
		$journal = $request->getJournal();
		$journalId = $journal->getId();
		$issue = $args[0];
		$issueId = $issue->getId();
		if ($request->getUserVar('sendIssueNotification')) {
			$request->_requestVars['sendIssueNotification'] =  NULL;
		} else {
			return false;
		}
		import('classes.notification.NotificationManager');
		$notificationManager = new NotificationManager();
		$notificationUsers = array();
		$userGroupDao = DAORegistry::getDAO('UserGroupDAO');
		$allUsers = $userGroupDao->getUsersByContextId($journalId);
		while ($user = $allUsers->next()) {
			if ($user->getDisabled()) continue;
			$notificationUsers[] = array('id' => $user->getId());
		}
		foreach ($notificationUsers as $userRole) {
			$notificationManager->createNotification(
					$request, $userRole['id'], NOTIFICATION_TYPE_PUBLISHED_ISSUE_WITH_TOC,
					$journalId, ASSOC_TYPE_ISSUE, $issueId
			);
		}
		return false;
	}
	
	/**
	 * Add the Table of Content in the email message
	 * @param string $hookname notificationmanager::getnotificationmessage
	 * @param array $args
	 * @return boolean False to continue execution
	 */
	function sendToc($hookname, $args) {
		$request = $this->getRequest();
		$notification = $args[0];
		$message =& $args[1];
		if ($notification->getType() == NOTIFICATION_TYPE_PUBLISHED_ISSUE_WITH_TOC) {
			if ($notification->getAssocType() == ASSOC_TYPE_ISSUE) {
				$issueId = $notification->getAssocId();
				$templateMgr = TemplateManager::getManager($request);
				$issueDao = DAORegistry::getDAO('IssueDAO');
				$templateMgr->assign('issue', $issueDao->getById($issueId));
				$message = $templateMgr->fetch('controllers/grid/issues/issueToc.tpl');
			}
		}
		return false;
	}
}
