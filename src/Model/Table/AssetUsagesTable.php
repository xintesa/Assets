<?php

namespace Xintesa\Assets\Model\Table;

/**
 * AssetUsages Table
 *
 */
class AssetUsagesTable extends AssetsAppTable {

	public $useTable = 'asset_usages';

	public $actsAs = array(
		'Croogo.Trackable',
	);

	public $belongsTo = array(
		'AssetsAsset' => array(
			'className' => 'Assets.AssetsAsset',
			'foreignKey' => 'asset_id',
		),
	);

	public function beforeSave($options = array()) {
		if (!empty($this->data['AssetsAssetUsage']['featured_image'])) {
			$this->data['AssetsAssetUsage']['type'] = 'FeaturedImage';
			unset($this->data['AssetsAssetUsage']['featured_image']);
		}
		return true;
	}

}
