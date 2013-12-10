<?php

$handlers = array();
if (Configure::read('Assets.installed')) {
	$handlers = array(
		'Assets.LegacyLocalAttachmentStorageHandler',
		'Assets.LocalAttachmentStorageHandler',
	);
}

$config = array(
	'EventHandlers' => $handlers,
);
