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
				$imageInfo = $this->__getImageInfo($path);
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
		$adapter = StorageManager::adapter($storage['adapter']);
		try {
			$raw = file_get_contents($file['tmp_name']);
			$extension = substr($file['name'], strrpos($file['name'], '.') + 1);

			$imageInfo = $this->__getImageInfo($file['tmp_name']);
			if (isset($imageInfo['mimeType'])) {
				$mimeType = $imageInfo['mimeType'];
			} else {
				$mimeType = $file['type'];
			}

			$result = $adapter->write($file['name'], $raw);
			$storage['filename'] = $file['name'];
			$storage['filesize'] = $file['size'];
			$storage['hash'] = sha1($raw);
			$storage['extension'] = $extension;
			$storage['mime_type'] = $mimeType;
			$storage['width'] = $imageInfo['width'];
			$storage['height'] = $imageInfo['height'];
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

	protected function _parentAsset($attachment) {
		$path = $attachment['AssetsAttachment']['import_path'];
		$parts = pathinfo($path);
		if (strpos($parts['filename'], '.') === false) {
			// old style, no resize indicator, dimension prepended
			list($size, $filename) = explode('_', $parts['filename'], 2);
		} else {
			// new style, with resize indicator appended before extension
			$filename = substr($parts['filename'], 0, strrpos($parts['filename'], '.'));
		}

		// strip cacheDir if found
		$dirname = $parts['dirname'];
		$pos = strpos($parts['dirname'], '/', 1);
		if ($pos !== false) {
			$dirname = substr($parts['dirname'], 0, $pos);
		}

		$filename = rtrim(WWW_ROOT, '/') . $dirname . '/' . $filename . '.' . $parts['extension'];
		$hash = sha1_file($filename);
		$this->Attachment->AssetsAsset->recursive = -1;
		return $this->Attachment->AssetsAsset->findByHash($hash);
	}

}
