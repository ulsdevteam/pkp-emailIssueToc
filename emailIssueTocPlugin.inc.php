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
		// The default notification won't be constructed with any useful association to the issue.
		// Cancel it.
		if ($request->getUserVar('sendIssueNotification')) {
			$request->_requestVars['sendIssueNotification'] =  NULL;
		} else {
			return false;
		}
		// Create our own notification, with the issue association
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
					$request, $userRole['id'], NOTIFICATION_TYPE_PUBLISHED_ISSUE,
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
		$application = Application::get();
		$request = $application->getRequest();
		$notification = $args[0];
		$message =& $args[1];
		$journal = $request->getJournal();
		if ($notification->getType() == NOTIFICATION_TYPE_PUBLISHED_ISSUE) {
			if ($notification->getAssocType() == ASSOC_TYPE_ISSUE) {
				$issueId = $notification->getAssocId();
				$issueDao = DAORegistry::getDAO('IssueDAO');
				$issue = $issueDao->getById($issueId);
				if ($issue) {
					$dispatcher = $application->getDispatcher();
					$originalRouter = $request->getRouter();
					$originalDispatcher = $request->getDispatcher();
					// The TemplateManager needs to see this Request based on a PageRouter, not the current ComponentRouter
					import('classes.core.PageRouter');
					$pageRouter = new PageRouter();
					$pageRouter->setApplication($application);
					$pageRouter->setDispatcher($dispatcher);
					$request->setRouter($pageRouter);
					$request->setDispatcher($dispatcher);
					$templateMgr = TemplateManager::getManager($request);
					$sections = Application::get()->getSectionDao()->getByIssueId($issueId);
					$issueSubmissionsInSection = [];
					foreach ($sections as $section) {
						$issueSubmissionsInSection[$section->getId()] = [
							'title' => $section->getLocalizedTitle(),
							'articles' => [],
						];
					}
					import('classes.submission.Submission');
					$allowedStatuses = [STATUS_PUBLISHED];
					if (!$issue->getPublished()) {
						$allowedStatuses[] = STATUS_SCHEDULED;
					}
					$issueSubmissions = iterator_to_array(Services::get('submission')->getMany([
						'contextId' => $journal->getId(),
						'issueIds' => [$issueId],
						'status' => $allowedStatuses,
						'orderBy' => 'seq',
						'orderDirection' => 'ASC',
					]));
					foreach ($issueSubmissions as $submission) {
						if (!$sectionId = $submission->getCurrentPublication()->getData('sectionId')) {
							continue;
						}
						$issueSubmissionsInSection[$sectionId]['articles'][] = $submission;
					}
					$templateMgr->assign('issue', $issue);
					$templateMgr->assign('publishedSubmissions', $issueSubmissionsInSection);
					$message = '<div>'.__('notification.type.issuePublished').'</div>';
					$message .= $templateMgr->fetch('frontend/objects/issue_toc.tpl');
					$request->setRouter($originalRouter);
					$request->setDispatcher($originalDispatcher);
				}
			}
		}
		return false;
	}
}
