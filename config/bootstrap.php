<?php

if (!Configure::read('Assets.installed')) {
	return;
}

spl_autoload_register(function($class) {
	$defaultPath = Plugin::path('Assets') . 'Vendor' . DS . 'Gaufrette' . DS . 'src' . DS;
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

Configure::write('Wysiwyg.uploadsPath', '');

Croogo::mergeConfig('Wysiwyg.actions', array(
	'AssetsAttachments/admin_browse',
));

StorageManager::config('LocalAttachment', array(
	'description' => 'Local Attachment',
	'adapterOptions' => array(WWW_ROOT . 'assets', true),
	'adapterClass' => '\Gaufrette\Adapter\Local',
	'class' => '\Gaufrette\Filesystem',
));
StorageManager::config('LegacyLocalAttachment', array(
	'description' => 'Local Attachment (Legacy)',
	'adapterOptions' => array(WWW_ROOT . 'uploads', true),
	'adapterClass' => '\Gaufrette\Adapter\Local',
	'class' => '\Gaufrette\Filesystem',
));

// TODO: make this configurable via backend
$actions = array(
	'Nodes/admin_edit',
	'Blocks/admin_edit',
	'Types/admin_edit',
);
$tabTitle = __d('assets', 'Assets');
foreach ($actions as $action):
	list($controller, ) = explode('/', $action);
	Croogo::hookAdminTab($action, $tabTitle, 'Assets.admin/asset_list');
	Croogo::hookHelper($controller, 'Assets.AssetsAdmin');
endforeach;

// TODO: make this configurable via backend
$models = array('Block', 'Node', 'Type');
foreach ($models as $model) {
	Croogo::hookBehavior($model, 'Assets.LinkedAssets', array('priority' => 9));
}

Croogo::hookHelper('*', 'Assets.AssetsFilter');
