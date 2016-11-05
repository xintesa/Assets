<?php

namespace Xintesa\Assets\Event;

use Cake\Log\Log;
use Cake\Event\EventListenerInterface;
use Croogo\Core\Nav;

/**
 * AssetsEventHandler
 *
 * @license  http://www.opensource.org/licenses/mit-license.php The MIT License
 */
class AssetsEventHandler implements EventListenerInterface {

/**
 * implementedEvents
 */
	public function implementedEvents() {
		return array(
			'Controller.AssetsAttachment.newAttachment' => array(
				'callable' => 'onNewAttachment',
			),
			'Croogo.setupAdminData' => array(
				'callable' => 'onSetupAdminData',
			),
			'Controller.Links.setupLinkChooser' => array(
				'callable' => 'onSetupLinkChooser',
			)
		);
	}

/**
 * Registers usage when new attachment is created and attached to a resource
 */
	public function onNewAttachment($event) {
		$controller = $event->subject;
		$request = $controller->request;
		$attachment = $event->data['attachment'];

		if (empty($request->data['AssetsAsset']['AssetsAssetUsage'])) {
			Log::error('No asset usage record to register');
			return;
		}

		$usage = $request->data['AssetsAsset']['AssetsAssetUsage'][0];
		$Usage = ClassRegistry::init('Assets.AssetsAssetUsage');
		$data = $Usage->create(array(
			'asset_id' => $attachment['AssetsAsset']['id'],
			'model' => $usage['model'],
			'foreign_key' => $usage['foreign_key'],
			'featured_image' => $usage['featured_image'],
		));
		$result = $Usage->save($data);
		if (!$result) {
			Log::error('Asset Usage registration failed');
			Log::error(print_r($Usage->validationErrors, true));
		}
		$event->result = $result;
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
		Nav::add('media.children.attachments', array(
			'title' => __d('croogo', 'Attachments'),
			'url' => array(
				'prefix' => 'admin',
				'plugin' => 'Xintesa/Assets',
				'controller' => 'Attachments',
				'action' => 'index',
			),
		));
	}

}
