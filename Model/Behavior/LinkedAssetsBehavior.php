<?php

App::uses('ModelBehavior', 'Model');

class LinkedAssetsBehavior extends ModelBehavior {

	public function setup(Model $model, $config = array()) {
		$model->bindModel(array(
			'hasMany' => array(
				'AssetsAssetUsage' => array(
					'className' => 'Assets.AssetsAssetUsage',
					'foreignKey' => 'foreign_key',
					'dependent' => true,
					'conditions' => array(
						'model' => $model->alias,
					),
				),
			),
		), false);
	}

	public function beforeFind(Model $model, $query) {
		if (isset($query['contain'])) {
			if (!isset($query['contain']['AssetsAssetUsage'])) {
				$query['contain']['AssetsAssetUsage'] = 'AssetsAsset';
			}
		}
		return $query;
	}

	public function afterFind(Model $model, $results, $primary = true) {
		if (!$primary) {
			return $results;
		}
		$key = 'LinkedAssets';
		foreach ($results as &$result) {
			$result[$key] = array();
			if (empty($result['AssetsAssetUsage'])) {
				unset($result['AssetsAssetUsage']);
				continue;
			}
			foreach ($result['AssetsAssetUsage'] as &$asset) {
				if (empty($asset['AssetsAsset'])) {
					continue;
				}
				if (empty($asset['type'])) {
					$result[$key]['DefaultAsset'][] = $asset['AssetsAsset'];
				} elseif ($asset['type'] === 'FeaturedImage') {
					$result[$key][$asset['type']] = $asset['AssetsAsset'];
				} else {
					$result[$key][$asset['type']][] = $asset['AssetsAsset'];
				}
			}
			unset($result['AssetsAssetUsage']);
		}
		return $results;
	}

}
