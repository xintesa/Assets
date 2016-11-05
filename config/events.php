<?php

use Cake\Core\Configure;

$handlers = array();
if (Configure::read('Assets.installed')) {
	$handlers = array(
		'Xintesa/Assets.AssetsEventHandler',
		'Xintesa/Assets.LegacyLocalAttachmentStorageHandler',
		'Xintesa/Assets.LocalAttachmentStorageHandler',
	);
}

$config = array(
	'EventHandlers' => $handlers,
);
