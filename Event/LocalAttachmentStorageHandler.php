<?php

App::uses('BaseStorageHandler', 'Assets.Event');
App::uses('CakeEventListener', 'Event');
App::uses('StorageManager', 'Assets.Lib');
App::uses('FileStorageUtils', 'Assets.Utility');

class LocalAttachmentStorageHandler extends BaseStorageHandler implements CakeEventListener {

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
				$imageInfo = $this->__getImageInfo($path);

				$fp = fopen($path, 'r');
				$stat = fstat($fp);
				$storage['filesize'] = $stat[7];
				$storage['filename'] = basename($path);
				$storage['hash'] = sha1_file($path);
				$storage['mime_type'] = $imageInfo['mimeType'];
				$storage['width'] = $imageInfo['width'];
				$storage['height'] = $imageInfo['height'];
				$storage['extension'] = substr($path, strrpos($path, '.') + 1);
			}
			return true;
		}

		$file = $storage['file'];
		$filesystem = StorageManager::adapter($storage['adapter']);
		try {
			$raw = file_get_contents($file['tmp_name']);
			$key = sha1($raw);
			$extension = strtolower(FileStorageUtils::fileExtension($file['name']));

			$imageInfo = $this->__getImageInfo($file['tmp_name']);
			if (isset($imageInfo['mimeType'])) {
				$mimeType = $imageInfo['mimeType'];
			} else {
				$mimeType = $file['type'];
			}

			if (empty($storage['path'])) {
				$prefix = FileStorageUtils::trimPath(FileStorageUtils::randomPath($file['name']));
			}
			$fullpath = $prefix . '/' . $key . '.' . $extension;
			$result = $filesystem->write($fullpath, $raw);
			$storage['path'] = '/assets/' . $fullpath;
			$storage['filename'] = $file['name'];
			$storage['filesize'] = $file['size'];
			$storage['hash'] = sha1($raw);
			$storage['mime_type'] = $mimeType;
			$storage['width'] = $imageInfo['width'];
			$storage['height'] = $imageInfo['height'];
			$storage['extension'] = $extension;
			return $result;
		} catch (Exception $e) {
			$this->log($e->getMessage());
			return false;
		}
	}

	public function onBeforeDelete($Event) {
		$model = $Event->subject();
		if (!$this->_check($Event)) {
			return true;
		}
		$model = $Event->subject();
		$fields = array('adapter', 'path');
		$data = $model->findById($model->id, $fields);
		$asset =& $data['AssetsAsset'];
		$filesystem = StorageManager::adapter($asset['adapter']);
		$key = str_replace('/assets', '', $asset['path']);
		if ($filesystem->has($key)) {
			$filesystem->delete($key);
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
		list($filename, ) = explode('.', $parts['filename'], 2);
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
