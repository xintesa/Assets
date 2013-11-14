<?php

App::uses('AssetsAppModel', 'Assets.Model');
App::uses('StorageManager', 'Assets.Lib');

class AssetsAsset extends AssetsAppModel {

	public $actsAs = array(
		'Croogo.Trackable',
	);

	public $useTable = 'assets';

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
