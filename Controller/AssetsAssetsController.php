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

		if (!empty($this->request->query)) {
			$query = $this->request->query;
			$conditions = array('AssetsAssetUsage.asset_id = AssetsAsset.id');
			if (isset($query['model'])) {
				$conditions['AssetsAssetUsage.model'] = $query['model'];
			}
			if (isset($query['foreign_key'])) {
				$conditions['AssetsAssetUsage.foreign_key'] = $query['foreign_key'];
			}
			$this->paginate['AssetsAsset']['joins'][] = array(
				'table' => 'asset_usages',
				'alias' => 'AssetsAssetUsage',
				'conditions' => $conditions,
			);
		}

		$this->set('attachments', $this->paginate());
	}

}
