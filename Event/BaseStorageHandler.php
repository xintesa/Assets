<?php

abstract class BaseStorageHandler extends Object {

	protected $_storage = null;

/**
 * Instance config
 */
	protected $_config = array();

/**
 * Constructor
 */
	public function __construct($config = array()) {
		$name = get_class($this);
		$config = Hash::merge(array(
			'alias' => $name,
			'className' => $name,
		), $config);
		$this->_config = $config;
		parent::__construct();
		$this->_storage = str_replace('StorageHandler', '', $config['alias']);
		$this->Attachment = ClassRegistry::init('Assets.AssetsAttachment');
	}

	protected abstract function _parentAsset($attachment);

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
 * Parse <img> tag and retrieves the value of the 'src' attribute
 */
	protected function _pathFromHtml($html) {
		$doc = new DOMDocument();
		$doc->loadHTML($html);
		$imgTags = $doc->getElementsByTagName('img');
		if ($imgTags->length == 0) {
			return;
		}
		return $imgTags->item(0)->getAttribute('src');
	}

/**
 * TODO: refactor this out and use Imagine in the future
 */
	protected function __getImageInfo($path) {
		if (!file_exists($path)) {
			return array();
		}

		$fp = finfo_open(FILEINFO_MIME_TYPE);
		$mimeType = finfo_file($fp, $path);

		switch ($mimeType) {
			case 'image/jpeg':
			case 'image/jpg':
			case 'image/png':
			case 'image/gif':
				$size = getimagesize($path);
				list($width, $height) = $size;
			break;
			default:
				$width = $height = null;
			break;
		}

		return compact('width', 'height', 'mimeType');
	}

/**
 * Registers a resized asset into the database
 *
 * Triggered by AssetsImageHelper::resize()
 */
	public function onResizeImage($Event) {
		if (!$this->_check($Event)) {
			return true;
		}
		if (!$Event->data['result']) {
			return true;
		}

		$src = $this->_pathFromHtml($Event->data['result']);
		$this->Attachment->contain('AssetsAsset');
		try {
			$filename = rtrim(WWW_ROOT, '/') . $src;
			$attachment = $this->Attachment->createFromFile($filename);
			if (is_string($attachment)) {
				return false;
			}
		} catch (InvalidArgumentException $e) {
			$this->log(get_class($this) . ': ' . $e->getMessage());
			return false;
		}

		return $this->_createAsset($attachment);
	}

/**
 * Create AssetsAsset record from $attachment when necessary
 */
	protected function _createAsset($attachment) {
		$hash = $attachment['AssetsAttachment']['hash'];
		$path = $attachment['AssetsAttachment']['import_path'];
		$Asset = $this->Attachment->AssetsAsset;
		$existing = $Asset->find('count', array(
			'conditions' => array(
				'OR' => array(
					$Asset->escapeField('hash') => $hash,
					$Asset->escapeField('path') => $path,
				),
			),
		));
		if ($existing > 0) {
			return false;
		}

		$parent = $this->_parentAsset($attachment);
		if (!$parent) {
			return false;
		}

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
