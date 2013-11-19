<?php

App::uses('AssetsAppModel', 'Assets.Model');

/**
 * AssetsAssetUsage Model
 *
 */
class AssetsAssetUsage extends AssetsAppModel {

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
