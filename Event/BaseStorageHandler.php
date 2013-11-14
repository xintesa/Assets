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

}
