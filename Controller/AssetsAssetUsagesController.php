<?php

App::uses('AssetsAppController', 'Assets.Controller');

class AssetsAssetUsagesController extends AssetsAppController {

	public $uses = array(
		'Assets.AssetsAssetUsage',
	);

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
				$this->AssetsAssetUsage->save($assetUsage);
			}
		}
		$this->redirect($this->referer());
	}

}
