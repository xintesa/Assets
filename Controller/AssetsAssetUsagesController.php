<?php

App::uses('AssetsAppController', 'Assets.Controller');

class AssetsAssetUsagesController extends AssetsAppController {

	public $uses = array(
		'Assets.AssetsAssetUsage',
	);

	public function beforeFilter() {
		parent::beforeFilter();

		$excludeActions = array(
			'admin_change_type',
		);
		if (in_array($this->request->params['action'], $excludeActions)) {
			$this->Security->validatePost = false;
			$this->Security->csrfCheck = false;
		}
	}

	public function admin_add() {
		if (isset($this->request->query)) {
			$assetId = $model = $foreignKey = $type = null;
			$assetId = $this->request->query['asset_id'];
			$model = $this->request->query['model'];
			$foreignKey = $this->request->query['foreign_key'];
			if (isset($this->request->query['type'])) {
				$type = $this->request->query['type'];
			}

			$conditions = array(
				'asset_id' => $assetId,
				'model' => $model,
				'foreign_key' => $foreignKey,
			);
			$exist = $this->AssetsAssetUsage->find('count', array(
				'recursive' => -1,
				'conditions' => $conditions,
			));
			if ($exist === 0) {
				$assetUsage = $this->AssetsAssetUsage->create(array(
					'asset_id' => $assetId,
					'model' => $model,
					'foreign_key' => $foreignKey,
					'type' => $type,
				));
				$saved = $this->AssetsAssetUsage->save($assetUsage);
				if ($saved) {
					$this->Session->setFlash('Asset added', 'default', array(
						'class' => 'success',
					));
				}
			} else {
				$this->Session->setFlash('Asset already exist', 'default', array(
					'class' => 'alert',
				));
			}
		}
		$this->redirect($this->referer());
	}

	public function admin_change_type() {
		$this->viewClass = 'Json';
		$result = true;
		if (isset($this->request->data['pk'])) {
			$data = $this->request->data;
			$this->AssetsAssetUsage->id = $data['pk'];
			$result = $this->AssetsAssetUsage->saveField('type', $data['value']);
		}
		$this->set(compact('result'));
		$this->set('_serialize', 'result');
	}

}