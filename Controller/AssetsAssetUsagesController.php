<?php

App::uses('AssetsAppController', 'Assets.Controller');

class AssetsAssetUsagesController extends AssetsAppController {

	public $uses = array(
		'Assets.AssetsAssetUsage',
	);

	public function beforeFilter() {
		parent::beforeFilter();

		$excludeActions = array(
			'admin_change_type', 'admin_unregister',
		);
		if (in_array($this->request->params['action'], $excludeActions)) {
			$this->Security->validatePost = false;
			$this->Security->csrfCheck = false;
		}
	}

	public function admin_add() {
		if (isset($this->request->query)) {
			$assetId = $model = $foreignKey = $type = null;
			$assetId = $this->request->query('asset_id');
			$model = $this->request->query('model');
			$foreignKey = $this->request->query('foreign_key');
			$type = $this->request->query('type');

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
					$this->Session->setFlash('Asset added', 'flash', array(
						'class' => 'success',
					));
				}
			} else {
				$this->Session->setFlash('Asset already exist', 'flash', array(
					'class' => 'warning',
				));
			}
		}
		$this->redirect($this->referer());
	}

	public function admin_change_type() {
		$this->viewClass = 'Json';
		$result = true;
		$data = array('pk' => null, 'value' => null);
		if (isset($this->request->data['pk'])) {
			$data = $this->request->data;
		} elseif (isset($this->request->query['pk'])) {
			$data = $this->request->query;
		}

		$id = $data['pk'];
		$value = $data['value'];

		if (isset($id)) {
			$this->AssetsAssetUsage->id = $id;
			$result = $this->AssetsAssetUsage->saveField('type', $value);
		}
		$this->set(compact('result'));
		$this->set('_serialize', 'result');
	}

	public function admin_unregister() {
		$this->viewClass = 'Json';
		$result = false;
		if (isset($this->request->data['id'])) {
			$result = $this->AssetsAssetUsage->delete($this->request->data['id']);
		}
		$this->set(compact('result'));
		$this->set('_serialize', 'result');
	}

}
