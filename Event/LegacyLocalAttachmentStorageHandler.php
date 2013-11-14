<?php

App::uses('BaseStorageHandler', 'Assets.Event');
App::uses('CakeEventListener', 'Event');
App::uses('StorageManager', 'Assets.Lib');

class LegacyLocalAttachmentStorageHandler extends BaseStorageHandler implements CakeEventListener {

	public function implementedEvents() {
		return array(
			'FileStorage.beforeSave' => 'onBeforeSave',
			'FileStorage.beforeDelete' => 'onBeforeDelete',
			'Assets.AssetsImageHelper.resize' => 'onResizeImage',
		);
	}

	public function onBeforeSave($Event) {
		if (!$this->_check($Event)) {
			return true;
		}

		$model = $Event->subject();
		$storage =& $model->data[$model->alias];

		if (empty($storage['file'])) {
			if (isset($storage['path']) && empty($storage['filename'])) {
				$path = rtrim(WWW_ROOT, '/') . $storage['path'];
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

	public function onBeforeDelete($Event) {
		if (!$this->_check($Event)) {
			return true;
		}
		$model = $Event->subject();
		$fields = array('adapter', 'filename');
		$data = $model->findById($model->id, $fields);
		$asset =& $data['AssetsAsset'];
		$adapter = StorageManager::adapter($asset['adapter']);
		if ($adapter->has($asset['filename'])) {
			$adapter->delete($asset['filename']);
		}
		return $model->deleteAll(array('parent_asset_id' => $model->id), true, true);
	}

	public function onResizeImage($Event) {
		if (!$this->_check($Event)) {
			return true;
		}
		if (!$Event->data['result']) {
			return true;
		}
		$doc = new DOMDocument();
		$doc->loadHTML($Event->data['result']);
		$imgTags = $doc->getElementsByTagName('img');
		if ($imgTags->length == 0) {
			return;
		}
		$src = $imgTags->item(0)->getAttribute('src');
		$Attachment = ClassRegistry::init('Assets.AssetsAttachment');
		$Asset =& $Attachment->AssetsAsset;
		$Attachment->contain('AssetsAsset');
		$attachment = $Attachment->createFromFile(rtrim(WWW_ROOT, '/') . $src);

		$hash = $attachment['AssetsAttachment']['hash'];

		$existing = $Asset->find('count', array(
			'conditions' => array($Asset->escapeField('hash') => $hash),
		));
		if ($existing > 0) {
			return true;
		}

		$path = $attachment['AssetsAttachment']['import_path'];
		$parts = pathinfo($path);
		if (strpos($parts['filename'], '.') === false) {
			list($filename, ) = explode('.', $parts['filename'], 2);
		} else {
			$filename = substr($parts['filename'], 0, strrpos($parts['filename'], '.'));
		}
		$hash = sha1_file(WWW_ROOT . $parts['dirname'] . '/' . $filename . '.' . $parts['extension']);

		$parent = $Attachment->findByHash($hash);

		$asset = $Asset->create(array(
			'parent_asset_id' => $parent['AssetsAsset']['id'],
			'model' => $parent['AssetsAsset']['model'],
			'foreign_key' => $parent['AssetsAsset']['foreign_key'],
			'adapter' => $parent['AssetsAsset']['adapter'],
			'path' => $path,
			'hash' => $hash,
		));

		return $Asset->save($asset);
	}

}
