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

	public $findMethods = array(
		'modelAssets' => true,
	);

	protected function _findModelAssets($state, $query = array(), $results = array()) {
		if ($state === 'after') {
			return $results;
		}
		$model = $foreignKey = null;
		if (isset($query['model'])) {
			$model = $query['model'];
			unset($query['model']);
		}
		if (isset($query['foreign_key'])) {
			$foreignKey = $query['foreign_key'];
			unset($query['foreign_key']);
		}
		$this->unbindModel(array('belongsTo' => array('AssetsAsset')));
		$this->bindModel(array(
			'hasOne' => array(
				'AssetsAsset' => array(
					'foreignKey' => false,
					'conditions' => array(
						'AssetsAsset.id = AssetsAssetUsage.asset_id',
					),
				),
				'AssetsAttachment' => array(
					'className' => 'Assets.AssetsAttachment',
					'foreignKey' => false,
					'conditions' => array(
						'AssetsAsset.model = \'AssetsAttachment\'',
						'AssetsAsset.foreign_key = AssetsAttachment.id',
					),
				),
			)
		));
		$query = Hash::merge($query, array(
			'conditions' => array(
				$this->escapeField('model') => $model,
				$this->escapeField('foreign_key') => $foreignKey,
			),
		));
		return $query;
	}

}
