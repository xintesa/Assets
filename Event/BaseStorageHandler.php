<?php

class BaseStorageHandler extends Object {

	protected $_storage = null;

	public function __construct() {
		parent::__construct();
		$this->_storage = str_replace('StorageHandler', '', get_class($this));
	}

	protected function _check($Event) {
		if (empty($Event->data['adapter'])) {
			return false;
		}
		return $this->_storage == $Event->data['adapter'];
	}

	public function storage() {
		return $this->_storage;
	}

/**
 * TODO: refactor this out and use Imagine in the future
 */
	protected function __getImageInfo($path) {
		if (!file_exists($path)) {
			return array();
		}
		$size = getimagesize($path);
		list($width, $height) = $size;
		$mimeType = $size['mime'];
		return compact('width', 'height', 'mimeType');
	}

}
