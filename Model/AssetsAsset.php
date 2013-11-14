<?php

App::uses('AssetsAppModel', 'Assets.Model');
App::uses('StorageManager', 'Assets.Lib');

class AssetsAsset extends AssetsAppModel {

	public $actsAs = array(
		'Croogo.Trackable',
	);

	public $useTable = 'assets';

	public $hasMany = array(
		'AssetsAssetUsage' => array(
			'className' => 'Assets.AssetsAssetUsage',
		),
	);

	public $belongsTo = array(
		'AssetsAttachment' => array(
			'className' => 'Assets.AssetsAttachment',
			'foreignKey' => 'foreign_key',
			'conditions' => array(
				'AssetsAsset.model' => 'AssetsAttachment',
			),
		),
	);

	public function beforeSave($options = array()) {
		$Event = Croogo::dispatchEvent('FileStorage.beforeSave', $this, array(
			'record' => $this->data,
			'adapter' => $this->data[$this->alias]['adapter'],
		));
		if ($Event->isStopped()) {
			return false;
		}
		return true;
	}

	public function beforeDelete($cascade) {
		if (!parent::beforeDelete($cascade)) {
			return false;
		}
		$Event = Croogo::dispatchEvent('FileStorage.beforeDelete', $this, array(
			'cascade' => $cascade,
			'adapter' => $this->field('adapter'),
		));
		if ($Event->isStopped()) {
			return false;
		}
		return true;
	}

}
