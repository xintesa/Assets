<?php

App::uses('CakeEventListener', 'Event');

class ImageProcessingHandler extends Object implements CakeEventListener {

	public function implementedEvents() {
		return array(
			'Assets.AssetsImageHelper.resize' => 'onResizeImage',
		);
	}

	public function onResizeImage($event) {
		if (!$event->data['result']) {
			return true;
		}
		$doc = new DOMDocument();
		$doc->loadHTML($event->data['result']);
		$imgTags = $doc->getElementsByTagName('img');
		if ($imgTags->length == 0) {
			return;
		}
		$src = $imgTags->item(0)->getAttribute('src');
		if ($src[0] == '/') {
			$src = substr($src, 1);
		}
		$Attachment = ClassRegistry::init('Assets.AssetsAttachment');
		$Asset =& $Attachment->AssetsAsset;
		$Attachment->contain('AssetsAsset');
		$attachment = $Attachment->createFromFile($src);

		$hash = $attachment['AssetsAttachment']['hash'];

		$existing = $Asset->find('count', array(
			'conditions' => array($Asset->escapeField('hash') => $hash),
		));
		if ($existing > 0) {
			return true;
		}

		$path = $attachment['AssetsAttachment']['import_path'];
		$parentPath = '/uploads/' . substr($path, strpos($path, '_') + 1);
		$parent = $Attachment->findByPath($parentPath);

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
