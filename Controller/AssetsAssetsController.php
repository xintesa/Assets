<?php

App::uses('AssetsAppController', 'Assets.Controller');

/**
 * Assets Controller
 *
 * @category Assets.Controller
 * @package  Assets.Controller
 * @author   Rachman Chavik <contact@xintesa.com>
 */
class AssetsAssetsController extends AssetsAppController {

/**
 * Models used by the Controller
 *
 * @var array
 * @access public
 */
	public $uses = array('Assets.AssetsAsset');

/**
 * Admin index
 *
 * @return void
 * @access public
 */
	public function admin_index() {
		$this->set('title_for_layout', __d('croogo', 'Attachments'));

		$this->AssetsAsset->recursive = 0;
		$this->paginate['AssetsAttachment']['order'] = 'AssetsAttachment.created DESC';
		$this->set('attachments', $this->paginate());
	}

/**
 * Admin browse
 *
 * @return void
 * @access public
 */
	public function admin_browse() {
		$this->layout = 'admin_popup';
		$this->set('title_for_layout', __d('croogo', 'Attachments'));

		$this->AssetsAsset->recursive = 0;
		$this->paginate['AssetsAttachment']['order'] = 'AssetsAttachment.created DESC';

		if (isset($this->request->query['model']) && isset($this->request->query['foriegn_key'])) {
			$this->paginate['AssetsAsset']['joins'][] = array(
				'table' => 'asset_usages',
				'alias' => 'AssetsAssetUsage',
				'conditions' => array(
					'AssetsAssetUsage.asset_id = AssetsAsset.id',
					'AssetsAssetUsage.model' => $this->request->query['model'],
					'AssetsAssetUsage.foreign_key' => $this->request->query['foreign_key'],
				),
			);
		}

		$this->set('attachments', $this->paginate());
	}

}
