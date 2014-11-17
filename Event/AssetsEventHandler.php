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
			'Controller.Links.setupLinkChooser' => array(
				'callable' => 'onSetupLinkChooser',
			)
		);
	}

	public function onSetupLinkChooser($event) {
		$linkChoosers = array();
		$linkChoosers['Images'] = array(
			'title' => 'Asset Image Attachments',
			'description' => 'Assets Attachments with image mime type',
			'url' => array(
				'plugin' => 'assets',
				'controller' => 'assets_attachments',
				'acion' => 'index',
				'?' => array(
					'chooser_type' => 'image',
					'chooser' => 1,
					'keepThis' => true,
					'TB_iframe' => true,
					'height' => '400',
					'width' => '600',
				)
			)
		);
		$linkChoosers['Files'] = array(
			'title' => 'Asset Files Attachments',
			'description' => 'Assets Attachments with other mime types, ie. pdf, xls, doc, etc.',
			'url' => array(
				'plugin' => 'assets',
				'controller' => 'assets_attachments',
				'acion' => 'index',
				'?' => array(
					'chooser_type' => 'file',
					'chooser' => 1,
					'keepThis' => true,
					'TB_iframe' => true,
					'height' => '400',
					'width' => '600',
				)
			)
		);

		Croogo::mergeConfig('Menus.linkChoosers', $linkChoosers);
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
