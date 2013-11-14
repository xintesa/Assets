<?php

App::uses('AssetsAppModel', 'Assets.Model');

/**
 * AssetsAttachment Model
 *
 */
class AssetsAttachment extends AssetsAppModel {

	public $useTable = 'attachments';

	public $actsAs = array(
		'Croogo.Trackable',
	);

	public $hasOne = array(
		'AssetsAsset' => array(
			'className' => 'Assets.AssetsAsset',
			'foreignKey' => 'foreign_key',
			'dependent' => true,
			'conditions' => array(
				'parent_asset_id' => null,
				'model' => 'AssetsAttachment',
			),
		),
	);

	public $findMethods = array(
		'duplicate' => true,
	);

/**
 * Find duplicates based on hash
 */
	protected function _findDuplicate($state, $query, $results = array()) {
		if ($state == 'before') {
			if (empty($query['hash'])) {
				return array();
			}
			$hash = $query['hash'];
			$query = Hash::merge($query, array(
				'type' => 'first',
				'recursive' => -1,
				'conditions' => array(
					$this->escapeField('hash') => $hash,
				),
			));
			unset($query['hash']);
			return $query;
		} else {
			return $results;
		}
	}

	public function beforeSave($options = array()) {
		if (isset($this->data['AssetsAsset']['file']['name'])) {
			$file = $this->data['AssetsAsset']['file'];
			$attachment =& $this->data[$this->alias];
			if (empty($attachment['title'])) {
				$attachment['title'] = $file['name'];
			}
			if (empty($attachment['slug'])) {
				$attachment['slug'] = $file['name'];
			}
			if (empty($attachment['hash'])) {
				$attachment['hash'] = sha1_file($file['tmp_name']);
			}
		}
		return true;
	}

/**
 * Create an AssetsAttachment data from $file
 *
 * @param $file string Path to file
 * @return array|string Array of data or error message
 * @throws InvalidArgumentException
 */
	public function createFromFile($file) {
		if (!file_exists($file)) {
			throw new InvalidArgumentException(__('%s cannot be found', $file));
		}

		$finfo = new finfo(FILEINFO_MIME_TYPE);
		$fp = fopen($file, 'r');
		$stat = fstat($fp);
		fclose($fp);
		$hash = sha1_file($file);
		$duplicate = isset($hash) ?
			$this->find('duplicate', array('hash' => $hash)) :
			false;
		if ($duplicate) {
			$firstDupe = $duplicate[0]['AssetsAttachment']['id'];
			return sprintf('%s is duplicate to asset: %s', str_replace(APP, '', $file), $firstDupe);
		}
		$path = str_replace(rtrim(WWW_ROOT, '/'), '', $file);
		$asset = $this->create(array(
			'path' => $path,
			'import_path' => $path,
			'title' => basename($file),
			'slug' => basename($file),
			'mime_type' => $finfo->file($file),
			'hash' => $hash,
			'status' => true,
			'created' => date('Y-m-d H:i:s', $stat[10]),
			'updated' => date('Y-m-d H:i:s', $stat[9]),
		));
		return $asset;
	}

/**
 * Create Import task
 */
	protected function _createImportTask($files, $options) {
		$data = array();
		$copy = array();
		$error = array();
		foreach ($files as $file) {
			$asset = $this->createFromFile($file);
			if (is_array($asset)) {
				$data[] = $asset;
				$copy[] = array('from' => $asset['AssetsAttachment']['import_path']);
				$error[] = null;
			} else {
				$data[] = null;
				$copy[] = null;
				$error[] = $asset;
			}
		}
		return compact('data', 'copy', 'error');
	}

/**
 * Perform the actual import based on $task
 *
 * @param $task array Array of tasks
 */
	public function runTask($task) {
		$imports = $errors = 0;
		foreach ($task['copy'] as $i => $source) {
			if (!$source) {
				continue;
			}
			$task['data'][$i]['AssetsAsset']['model'] = $this->alias;
			$task['data'][$i]['AssetsAsset']['adapter'] = 'LegacyLocalAttachment';
			$task['data'][$i]['AssetsAsset']['path'] = $source['from'];
			$result = $this->saveAll($task['data'][$i], array('atomic' => true));
			if ($result) {
				$imports++;
			} else {
				$errors++;
			}
		}
		return compact('imports', 'errors');
	}

/**
 * Import files into the assets repository
 *
 * @param $dir array|string Path to import
 * @param $regex string Regex to filter files to import
 * @param $options array
 * @throws InvalidArgumentException
 */
	public function importTask($dirs = array(), $regex = '.*', $options = array()) {
		$options = Hash::merge(array(
			'recursive' => false,
		), $options);
		foreach ($dirs as $dir) {
			if (substr($dir, -1) === '/') {
				$dir = substr($dir, 0, strlen($dir) - 1);
			}
			if (!is_dir($dir)) {
				throw new InvalidArgumentException(__('%s is not a directory', $dir));
			}
			$folder = new Folder($dir, false, false);
			if ($options['recursive']) {
				$files = $folder->findRecursive($regex, false);
			} else {
				$files = $folder->find($regex, false);
				$files = array_map(
					function($v) use ($dir) {
						return APP . $dir . '/' . $v;
					},
					$files
				);
			}

			return $this->_createImportTask($files, $options);
		}
	}

}
