<?php

spl_autoload_register(function($class) {
	$defaultPath = CakePlugin::path('Assets') . 'Vendor' . DS . 'Gaufrette' . DS . 'src' . DS;
	$base = Configure::read('Assets.GaufretteLib');
	if (empty($base)) {
		$base = $defaultPath;
	}
	$class = str_replace('\\', DS, $class);
	if (file_exists($base . $class . '.php')) {
		include ($base . $class . '.php');
	}
});

Configure::write('Wysiwyg.attachmentBrowseUrl', array(
	'plugin' => 'assets',
	'controller' => 'assets_attachments',
	'action' => 'browse',
));

Croogo::mergeConfig('Wysiwyg.actions', array(
	'AssetsAttachments/admin_browse',
));

App::uses('StorageManager', 'Assets.Lib');
StorageManager::config('Assets', array(
	'adapterOptions' => array(WWW_ROOT . 'uploads', true),
	'adapterClass' => '\Gaufrette\Adapter\Local',
	'class' => '\Gaufrette\Filesystem',
));

// TODO: This needs to be dynamic
Croogo::hookBehavior('AssetsAsset', 'Assets.LocalAttachment');

CroogoNav::add('media.children.attachments', array(
	'title' => __d('croogo', 'Attachments'),
	'url' => array(
		'admin' => true,
		'plugin' => 'assets',
		'controller' => 'assets_attachments',
		'action' => 'index',
	),
));
