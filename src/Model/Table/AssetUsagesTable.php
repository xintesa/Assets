<?php

namespace Xintesa\Assets\Model\Table;

/**
 * AssetUsages Table
 *
 */
class AssetUsagesTable extends AssetsAppTable {

	public function initialize(array $config) {
		parent::initialize($config);
		$this->table('asset_usages');

		$this->belongsTo('Assets', [
			'className' => 'Xintesa/Assets.Assets',
			'foreignKey' => 'asset_id',
		]);

		$this->addBehavior('Croogo/Core.Trackable');
	}

	public function beforeSave($options = array()) {
		if (!empty($this->data['AssetsAssetUsage']['featured_image'])) {
			$this->data['AssetsAssetUsage']['type'] = 'FeaturedImage';
			unset($this->data['AssetsAssetUsage']['featured_image']);
		}
		return true;
	}

}
