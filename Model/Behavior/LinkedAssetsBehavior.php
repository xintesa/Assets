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

		if (isset($model->AssetsAsset)) {
			$Asset = $model->AssetsAsset;
		} else {
			$Asset = ClassRegistry::init('Assets.AssetsAsset');
		}
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

					$seedId = isset($asset['AssetsAsset']['parent_asset_id']) ?
						$asset['AssetsAsset']['parent_asset_id'] :
						$asset['AssetsAsset']['id'];
					$relatedAssets = $Asset->find('all', array(
						'recursive' => -1,
						'order' => 'width DESC',
						'conditions' => array(
							'AssetsAsset.parent_asset_id' => $seedId,
						),
					));
					foreach ($relatedAssets as $related) {
						$result[$key]['FeaturedImage']['Versions'][] = $related['AssetsAsset'];
					}

				} else {
					$result[$key][$asset['type']][] = $asset['AssetsAsset'];
				}
			}
			unset($result['AssetsAssetUsage']);
		}
		return $results;
	}

}
