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
			'dependent' => true,
		),
	);

	public $validate = array(
		'file' => 'checkFileUpload'
	);

	public $belongsTo = array(
		'AssetsAttachment' => array(
			'className' => 'Assets.AssetsAttachment',
			'foreignKey' => 'foreign_key',
			'conditions' => array(
				'AssetsAsset.model' => 'AssetsAttachment',
			),
			'counterCache' => 'asset_count',
			'counterScope' => array(
				'AssetsAsset.model' => 'AssetsAttachment',
			),
		),
	);

	public function beforeSave($options = array()) {
		$adapter = isset($this->data[$this->alias]['adapter']) ?
			$this->data[$this->alias]['adapter'] :
			null;
		$Event = Croogo::dispatchEvent('FileStorage.beforeSave', $this, array(
			'record' => $this->data,
			'adapter' => $adapter,
		));
		if ($Event->isStopped()) {
			return false;
		}
		return true;
	}

	public function beforeDelete($cascade = true) {
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

	public function checkFileUpload($check) {
		switch($check['file']['error']){
			case UPLOAD_ERR_INI_SIZE:
				return 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
			break;
			case UPLOAD_ERR_FORM_SIZE:
				return 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
			break;
			case UPLOAD_ERR_PARTIAL:
				return 'The uploaded file was only partially uploaded.';
			break;
			case UPLOAD_ERR_NO_FILE:
				return 'No file was uploaded.';
			break;
			case UPLOAD_ERR_NO_TMP_DIR:
				return 'Missing a temporary folder.';
			break;
			case UPLOAD_ERR_CANT_WRITE:
				return 'Failed to write file to disk.';
			break;
			case UPLOAD_ERR_EXTENSION:
				return 'A PHP extension stopped the file upload.';
			break;
			case UPLOAD_ERR_OK:
				return true;
			break;
		}
	}

}
