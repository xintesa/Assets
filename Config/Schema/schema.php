<?php

class AssetSchema extends CakeSchema {

	public function before($event = array()) {
		return true;
	}

	public function after($event = array()) {
	}

	public $attachments = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary'),
		'title' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_unicode_ci', 'charset' => 'utf8'),
		'slug' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_unicode_ci', 'charset' => 'utf8'),
		'body' => array('type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8_unicode_ci', 'charset' => 'utf8'),
		'excerpt' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_unicode_ci', 'charset' => 'utf8'),
		'status' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'sticky' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'visibility_roles' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_unicode_ci', 'charset' => 'utf8'),
		'hash' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 64),
		'plugin' => array('type' => 'string', 'null' => true, 'default' => null),
		'import_path' => array('type' => 'string', 'length' => 512),
		'created' => array('type' => 'datetime', 'null' => true),
		'created_by' => array('type' => 'integer', 'null' => true),
		'updated' => array('type' => 'datetime', 'null' => true),
		'updated_by' => array('type' => 'integer', 'null' => true),
		'indexes' => array(
			'PRIMARY' => array('column' => array('id'), 'unique' => true),
			'ix_attachments_hash' => array('column' => array('hash')),
		),
		'tableParameters' => array(
			'charset' => 'utf8',
			'collate' => 'utf8_unicode_ci',
			'engine' => 'InnoDb',
		),
	);

/**
 * Schema for assets table
 *
 * @var array
 */
	public $assets = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary'),
		'parent_asset_id' => array('type' => 'integer', 'null' => true, 'default' => null),
		'foreign_key' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 36),
		'model' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 64),
		'filename' => array('type' => 'string', 'null' => false, 'default' => null),
		'filesize' => array('type' => 'integer', 'null' => true, 'default' => null, 'length' => 16),
		'width' => array('type' => 'integer', 'null' => true, 'default' => null, 'length' => 16),
		'height' => array('type' => 'integer', 'null' => true, 'default' => null, 'length' => 16),
		'mime_type' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 32),
		'extension' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 5),
		'hash' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 64),
		'path' => array('type' => 'string', 'null' => false, 'default' => null),
		'adapter' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 32, 'comment' => 'Gaufrette Storage Adapter Class'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'ix_assets_hash' => array('column' => array('hash', 'path')),
			'fk_assets' => array('column' => array('model', 'foreign_key')),
			'un_assets_dimension' => array(
				'unique' => true,
				'column' => array('parent_asset_id', 'width', 'height'),
			),
		),
	);

	public $asset_usages = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary'),
		'asset_id' => array('type' => 'integer', 'null' => false, 'default' => null),
		'model' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 64),
		'foreign_key' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 36),
		'type' => array('type' => 'string', 'length' => 20, 'null' => true, 'default' => null),
		'url' => array('type' => 'string', 'length' => 512, 'null' => true),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'params' => array('type' => 'text', 'null' => true, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'fk_asset_usage' => array('column' => array('model', 'foreign_key')),
		),
	);
}
