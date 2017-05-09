<?php

namespace Xintesa\Assets\Controller\Admin;

use Cake\Event\Event;

class AssetUsagesController extends AppController {

	public $uses = array(
		'Assets.AssetsAssetUsage',
	);

	public function beforeFilter(Event $event) {
		parent::beforeFilter($event);

		$excludeActions = array(
			'change_type', 'unregister',
		);
		if (in_array($this->request->params['action'], $excludeActions)) {
			$this->Security->config('validatePost', false);
			$this->eventManager()->off($this->Csrf);
		}
	}

	public function add() {
		if (isset($this->request->query)) {
			$assetId = $this->request->query('asset_id');
			$model = $this->request->query('model');
			$foreignKey = $this->request->query('foreign_key');
			$type = $this->request->query('type');

			$conditions = array(
				'asset_id' => $assetId,
				'model' => $model,
				'foreign_key' => $foreignKey,
			);
			$exist = $this->AssetUsages->find()
				->where($conditions)
				->count();
			if ($exist === 0) {
				$assetUsage = $this->AssetUsages->newEntity([
					'asset_id' => $assetId,
					'model' => $model,
					'foreign_key' => $foreignKey,
					'type' => $type,
				]);
				$saved = $this->AssetUsages->save($assetUsage);
				if ($saved) {
					$this->Flash->success('Asset added');
				}
			} else {
				$this->Flash->error('Asset already exist');
			}
		}
		$this->redirect($this->referer());
	}

	public function change_type() {
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

	public function unregister() {
		$this->viewBuilder()->className('Json');
		$result = false;
		if ($id = $this->request->getData('id')) {
			$assetUsage = $this->AssetUsages->get($id);
			$result = $this->AssetUsages->delete($assetUsage);
		}
		$this->set(compact('result'));
		$this->set('_serialize', 'result');
	}

}
