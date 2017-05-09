<?php

namespace Xintesa\Assets\Model\Table;

use ArrayObject;
use Cake\Event\Event;
use Cake\Datasource\EntityInterface;
use Croogo\Core\Croogo;

class AssetsTable extends AssetsAppTable {

	public $validate = array(
		'file' => 'checkFileUpload'
	);

	public function initialize(array $config) {
		parent::initialize($config);

		$this->table('assets');

		$this->hasMany('AssetUsages', [
			'className' => 'Xintesa/Assets.AssetUsages',
			'dependent' => true,
		]);

		$this->belongsTo('Attachments', [
			'className' => 'Assets.Attachments',
			'foreignKey' => 'foreign_key',
			'conditions' => [
				'AssetsAsset.model' => 'Attachments',
			],
			'counterCache' => 'asset_count',
			'counterScope' => [
				'AssetsAsset.model' => 'Attachments',
			],
		]);

		$this->addBehavior('Search.Search');
		$this->addBehavior('Croogo/Core.Trackable');

	}

	public function beforeSave(Event $event, EntityInterface $entity, ArrayObject $options = null) {
		$adapter = $entity->get('adapter');
		if (!$entity->filename) {
			$entity->filename = '';
		}
		if (!$entity->path) {
			$entity->path = '';
		}
		$Event = Croogo::dispatchEvent('FileStorage.beforeSave', $this, array(
			'record' => $entity,
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