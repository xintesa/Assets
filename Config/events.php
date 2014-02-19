<?php

$handlers = array();
if (Configure::read('Assets.installed')) {
	$handlers = array(
		'Assets.AssetsEventHandler',
		'Assets.LegacyLocalAttachmentStorageHandler',
		'Assets.LocalAttachmentStorageHandler',
	);
}

$config = array(
	'EventHandlers' => $handlers,
);
