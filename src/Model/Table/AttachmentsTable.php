<?php

namespace Xintesa\Assets\Model\Table;

use ArrayObject;
use Cake\ORM\Query;
use Cake\Event\Event;
use Cake\Datasource\EntityInterface;
use Cake\Utility\Hash;
use Croogo\Core\Croogo;
use Xintesa\Assets\Model\Table\AssetsAppTable;

/**
 * Attachments Model
 *
 */
class AttachmentsTable extends AssetsAppTable {

	public $filterArgs = array(
		'filter' => array('type' => 'query', 'method' => 'filterAttachments'),
		'type' => array('type' => 'value', 'field' => 'AssetsAssetUsage.type'),
	);

	public $findMethods = array(
		'duplicate' => true,
		'modelAttachments' => true,
		'versions' => true,
	);

	public function initialize(array $config) {
		parent::initialize($config);

		$this->table('attachments');

		$this->hasOne('Assets', [
			'className' => 'Xintesa/Assets.Assets',
			'foreignKey' => 'foreign_key',
			'dependent' => true,
			'conditions' => [
				'parent_asset_id IS' => null,
				'model' => 'Attachments',
			],
		]);

		$this->addBehavior('Croogo/Core.Trackable');
		$this->addBehavior('Search.Search');
		//$this->addBehavior('Burzum/Imagine.Imagine');

		$this->searchManager()
			->add('filename', 'Search.Like', [
				'field' => $this->Assets->aliasField('filename'),
				'before' => true,
				'after' => true,
			])
			->value('model', [
				'field' => $this->Assets->AssetUsages->aliasField('model'),
			])
			->value('foreign_key', [
				'field' => $this->Assets->AssetUsages->aliasField('foreign_key'),
			])
			->value('asset_id', [
				'field' => $this->Assets->aliasField('id'),
			])
			->value('id', [
				'field' => $this->aliasField('id'),
			])
			->value('type', [
				'field' => $this->Assets->AssetUsages->aliasField('type'),
			]);
	}

	public function filterAttachments($data = array()) {
		$conditions = array();
		if (!empty($data['filter'])) {
			$filter = '%' . $data['filter'] . '%s';
			$conditions = array(
				'OR' => array(
					$this->escapeField('title') . ' LIKE' => $filter,
					$this->escapeField('excerpt') . ' LIKE' => $filter,
					$this->escapeField('body') . ' LIKE' => $filter,
				),
			);
		}
		return $conditions;
	}

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

	public function findModelAttachments(Query $query, array $options) {
		$model = $foreignKey = null;
		if (isset($options['model'])) {
			$model = $options['model'];
			unset($options['model']);
		}
		if (isset($options['foreign_key'])) {
			$foreignKey = $options['foreign_key'];
			unset($options['foreign_key']);
		}
		$this->associations()->remove('Assets');
		$this->addAssociations([
			'hasOne' => [
				'Assets' => [
					'className' => 'Xintesa/Assets.Assets',
					'foreignKey' => false,
					'conditions' => [
						'Assets.model = \'Attachments\'',
						'Assets.foreign_key = Attachments.id',
					],
				],
				'AssetUsages' => [
					'className' => 'Xintesa/Assets.AssetUsages',
					'foreignKey' => false,
					'conditions' => [
						'Assets.id = AssetUsages.asset_id',
					],
				],
			]
		]);
		$query->contain('Assets');
		$query->contain('AssetUsages');

		if (isset($model) && isset($foreignKey)) {
			$query->where([
				'AssetUsages.model' => $model,
				'AssetUsages.foreign_key' => $foreignKey,
			]);
		}

		return $query;
	}

	public function findVersions(Query $query, array $options) {
		$assetId = $model = $foreignKey = null;
		if (isset($options['asset_id'])) {
			$assetId = $options['asset_id'];
			unset($options['asset_id']);
		}
		if (isset($options['model'])) {
			$model = $options['model'];
			unset($options['model']);
		}
		if (isset($options['foreign_key'])) {
			$foreignKey = $options['foreign_key'];
			unset($options['foreign_key']);
		}
		if (isset($options['all'])) {
			$all = $options['all'];
			unset($options['all']);
		}
		$this->associations()->remove('Assets');
		$this->addAssociations([
			'hasOne' => [
				'Assets' => array(
					'className' => 'Xintesa/Assets.Assets',
					'foreignKey' => false,
					'conditions' => array(
						'Assets.model = \'Attachments\'',
						'Assets.foreign_key = Attachments.id',
					),
				),
				'AssetUsages' => [
					'className' => 'Xintesa/Assets.AssetUsages',
					'foreignKey' => false,
					'conditions' => [
						'Assets.id = AssetUsages.asset_id',
					],
				],
			]
		]);
		$contain = isset($options['contain']) ? $options['contain'] : array();
		$contain = Hash::merge($contain, [
			'Assets',
			'AssetUsages',
		]);
		$query->contain($contain);
		if ($assetId && !isset($all)) {
			$conditions = Hash::merge($options['conditions'], [
				'OR' => [
					'Assets.id' => $assetId,
					'Assets.parent_asset_id' => $assetId,
				],
			]);
			$query->where($conditions);
		}
		return $query;
	}

	use \Cake\Log\LogTrait;

	public function beforeSave(Event $event, EntityInterface $entity, ArrayObject $options = null) {
		if (!empty($entity->asset->file['name'])) {
			$file = $entity->asset->file;
			$attachment = $entity;
			if (empty($attachment->title)) {
				$attachment->title = $file['name'];
			}
			if (empty($attachment->slug)) {
				$attachment->slug = $file['name'];
			}
			if (empty($attachment->hash)) {
				$attachment->hash = sha1_file($file['tmp_name']);
			}
		}
		return true;
	}

/**
 * Create an Attachments data from $file
 *
 * @param $file string Path to file
 * @return array|string Array of data or error message
 * @throws InvalidArgumentException
 */
	public function createFromFile($file) {
		if (!file_exists($file)) {
			throw new InvalidArgumentException(__('{0} cannot be found', $file));
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
			$firstDupe = $duplicate[0]['Attachments']['id'];
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
			'created' => date('Y-m-d H:i:s', $stat[9]),
			'updated' => date('Y-m-d H:i:s', time()),
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
				$copy[] = array('from' => $asset['Attachments']['import_path']);
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
				throw new InvalidArgumentException(__('{0} is not a directory', $dir));
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

/**
 * Create a video thumbnail
 *
 * @param integer $id Attachment Id
 * @param integer $w New Width
 * @param integer $h New Height
 * @param array $options Options array
 */
	public function createVideoThumbnail($id, $w, $h, $options = array()) {
		if (!class_exists('FFmpegMovie')) {
			throw new RunTimeException('FFmpegMovie class not found');
		}
		$this->recursive = -1;
		$this->contain(array('AssetsAsset'));
		$attachment = $this->findById($id);
		$asset =& $attachment['AssetsAsset'];
		$path = rtrim(WWW_ROOT, '/') . $asset['path'];

		$info = pathinfo($asset['path']);
		$ind = sprintf('.resized-%dx%d.', $w, $h);

		$uploadsDir = str_replace('/' . $options['uploadsDir'] . '/', '', dirname($asset['path'])) . '/';
		$filename = $info['filename'] . $ind . 'jpg';
		$writePath = WWW_ROOT . 'galleries' . DS . $uploadsDir . $filename;

		$ffmpeg = new FFmpegMovie($path, null, 'avconv');
		$frame = $ffmpeg->getFrame(null, 200, 150);
		imagejpeg($frame->toGDImage(), $writePath, 100);

		$fp = fopen($writePath, 'r');
		$stat = fstat($fp);
		fclose($fp);

		$adapter = $asset['adapter'];

		$data = $this->AssetsAsset->create(array(
			'filename' => $filename,
			'path' => dirname($asset['path']) . '/' . $filename,
			'model' => $asset['model'],
			'extension' => $asset['extension'],
			'parent_asset_id' => $asset['id'],
			'foreign_key' => $asset['foreign_key'],
			'adapter' => $adapter,
			'mime_type' => $asset['mime_type'],
			'width' => $newWidth,
			'height' => $newHeight,
			'filesize' => $stat[7],
		));

		$asset = $this->AssetsAsset->save($data);
		return $asset;
	}

/**
 * Copy an existing attachment and resize with width: $w and height: $h
 *
 * @param integer $id Attachment Id
 * @param integer $w New Width
 * @param integer $h New Height
 * @param array $options Options array
 */
	public function createResized($id, $w, $h, $options = array()) {
		$options = Hash::merge(array(
			'uploadsDir' => 'assets',
		), $options);
		$imagine = $this->imagineObject();
		$this->recursive = -1;
		$this->contain(array('AssetsAsset'));
		$attachment = $this->findById($id);
		$asset =& $attachment['AssetsAsset'];
		$path = rtrim(WWW_ROOT, '/') . $asset['path'];

		$image = $imagine->open($path);
		$size = $image->getSize();
		$width = $size->getWidth();
		$height = $size->getHeight();

		if (empty($h) && !empty($w)) {
			$scale = $w / $width;
			$newSize = $size->scale($scale);
		} elseif (empty($w) && !empty($h)) {
			$scale = $h / $height;
			$newSize = $size->scale($scale);
		} else {
			$scaleWidth = $w / $width;
			$scaleHeight = $h / $height;
			$scale = $scaleWidth > $scaleHeight ? $scaleWidth : $scaleHeight;
			$newSize = $size->scale($scale);
		}

		$newWidth = $newSize->getWidth();
		$newHeight = $newSize->getHeight();

		$image->resize($newSize);

		$tmpName = tempnam('/tmp', 'qq');
		$image->save($tmpName, array('format' => $asset['extension']));

		$fp = fopen($tmpName, 'r');
		$stat = fstat($fp);
		fclose($fp);

		$raw = file_get_contents($tmpName);
		unlink($tmpName);

		$info = pathinfo($asset['path']);
		$ind = sprintf('.resized-%dx%d.', $newWidth, $newHeight);

		$uploadsDir = str_replace('/' . $options['uploadsDir'] . '/', '', dirname($asset['path'])) . '/';
		$filename = $info['filename'] . $ind . $info['extension'];
		$writePath = $uploadsDir . $filename;

		$adapter = $asset['adapter'];
		$filesystem = StorageManager::adapter($adapter);
		$filesystem->write($writePath, $raw);

		$data = $this->AssetsAsset->create(array(
			'filename' => $filename,
			'path' => dirname($asset['path']) . '/' . $filename,
			'model' => $asset['model'],
			'extension' => $asset['extension'],
			'parent_asset_id' => $asset['id'],
			'foreign_key' => $asset['foreign_key'],
			'adapter' => $adapter,
			'mime_type' => $asset['mime_type'],
			'width' => $newWidth,
			'height' => $newHeight,
			'filesize' => $stat[7],
		));

		$asset = $this->AssetsAsset->save($data);
		return $asset;
	}

}