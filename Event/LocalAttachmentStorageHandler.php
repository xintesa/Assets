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

/**
 * Find parent of the resized image
 */
	protected function _parentAsset($attachment) {
		$path = $attachment['AssetsAttachment']['import_path'];
		$parts = pathinfo($path);
		list($filename, ) = explode('.', $parts['filename'], 2);
		$filename = rtrim(WWW_ROOT, '/') . $parts['dirname'] . '/' . $filename . '.' . $parts['extension'];
		$hash = sha1_file($filename);
		$this->Attachment->AssetsAsset->recursive = -1;
		return $this->Attachment->AssetsAsset->findByHash($hash);
	}

}
