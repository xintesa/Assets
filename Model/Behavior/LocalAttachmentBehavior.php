<?php

App::uses('ModelBehavior', 'Model');
App::uses('StorageManager', 'Assets.Lib');

class LocalAttachmentBehavior extends ModelBehavior {

	public function setup(Model $model, $config = array()) {
		$this->settings[$model->alias] = $config;
	}

	protected function _filestat($path, &$data) {
	}

	public function beforeSave(Model $model, $options = array()) {
		$setting = $this->settings[$model->alias];
		$storage =& $model->data[$model->alias];

		if (empty($storage['file'])) {
			if (isset($storage['path']) && empty($storage['filename'])) {
				$path = WWW_ROOT . $storage['path'];
				$fp = fopen($path, 'r');
				$stat = fstat($fp);
				$finfo = new finfo(FILEINFO_MIME_TYPE);
				$storage['filesize'] = $stat[7];
				$storage['filename'] = basename($path);
				$storage['hash'] = sha1_file($path);
				$storage['mime_type'] = $finfo->file($path);
				$storage['extension'] = substr($path, strrpos($path, '.') + 1);
			}
			return true;
		}

		$file = $storage['file'];
		$adapter = StorageManager::adapter($storage['adapter']);
		try {
			$raw = file_get_contents($file['tmp_name']);
			$extension = substr($file['name'], strrpos($file['name'], '.') + 1);
			$result = $adapter->write($file['name'], $raw);
			$storage['filename'] = $file['name'];
			$storage['filesize'] = $file['size'];
			$storage['hash'] = sha1($raw);
			$storage['extension'] = $extension;
			$storage['mime_type'] = $file['type'];
			if (empty($storage['path'])) {
				$storage['path'] = '/uploads/' . $file['name'];
			}
			return $result;
		} catch (Exception $e) {
			$this->log($e->getMessage());
			return false;
		}
	}

	public function beforeDelete(Model $model, $cascade = true) {
		$fields = array('adapter', 'path');
		$data = $model->findById($model->id, $fields);
		$asset =& $data['AssetsAsset'];
		StorageManager::adapter($asset['adapter'])->delete(
			str_replace('/uploads/', '', $asset['path'])
		);
		return $model->deleteAll(array('parent_asset_id' => $model->id), true, true);
	}

}
