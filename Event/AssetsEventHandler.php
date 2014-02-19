<?php

App::uses('CakeEventListener', 'Event');

/**
 * AssetsEventHandler
 *
 * @license  http://www.opensource.org/licenses/mit-license.php The MIT License
 */
class AssetsEventHandler implements CakeEventListener {

/**
 * implementedEvents
 */
	public function implementedEvents() {
		return array(
			'Croogo.setupAdminData' => array(
				'callable' => 'onSetupAdminData',
			),
		);
	}

/**
 * Setup admin data
 */
	public function onSetupAdminData($event) {
		CroogoNav::add('media.children.attachments', array(
			'title' => __d('croogo', 'Attachments'),
			'url' => array(
				'admin' => true,
				'plugin' => 'assets',
				'controller' => 'assets_attachments',
				'action' => 'index',
			),
		));
	}

}
