<?php

$this->extend('/Common/admin_edit');

$this->Html
	->addCrumb('', '/admin', array('icon' => 'home'))
	->addCrumb(__d('croogo', 'Attachments'), array('plugin' => 'assets', 'controller' => 'assets_attachments', 'action' => 'index'))
	->addCrumb($this->data['AssetsAttachment']['title'], '/' . $this->request->url);

if ($this->layout === 'admin_popup'):
	$this->append('title', ' ');
endif;

$formUrl = array('controller' => 'assets_attachments', 'action' => 'edit');
if (isset($this->request->query)) {
	$formUrl = array_merge($formUrl, $this->request->query);
}

$this->append('form-start', $this->Form->create('AssetsAttachment', array(
	'url' => $formUrl,
)));

$this->append('tab-heading');
	echo $this->Croogo->adminTab(__d('croogo', 'Attachment'), '#attachment-main');
	echo $this->Croogo->adminTabs();
$this->end();

$this->append('tab-content');
	echo $this->Html->tabStart('attachment-main');
		echo $this->Form->input('id');

		echo $this->Form->input('title', array(
			'label' => __d('croogo', 'Title'),
		));
		echo $this->Form->input('excerpt', array(
			'label' => __d('croogo', 'Caption'),
		));

		echo $this->Form->input('file_url', array(
			'label' => __d('croogo', 'File URL'),
			'value' => Router::url($this->data['AssetsAsset']['path'], true),
			'readonly' => 'readonly')
		);

		echo $this->Form->input('file_type', array(
			'label' => __d('croogo', 'Mime Type'),
			'value' => $this->data['AssetsAsset']['mime_type'],
			'readonly' => 'readonly')
		);
	echo $this->Html->tabEnd();

	echo $this->Croogo->adminTabs();
$this->end();

$this->append('panels');
	$redirect = array('action' => 'index');
	if ($this->Session->check('Wysiwyg.redirect')) {
		$redirect = $this->Session->read('Wysiwyg.redirect');
	}
	if (isset($this->request->query['model'])) {
		$redirect = array_merge(
			array('action' => 'browse'),
			array('?' => $this->request->query)
		);
	}
	echo $this->Html->beginBox(__d('croogo', 'Publishing')) .
		$this->Form->button(__d('croogo', 'Save')) .
		$this->Html->link(
			__d('croogo', 'Cancel'),
			$redirect,
			array('class' => 'cancel', 'button' => 'danger')
		);
	echo $this->Html->endBox();

	$fileType = explode('/', $this->data['AssetsAsset']['mime_type']);
	$fileType = $fileType['0'];
	$path = $this->data['AssetsAsset']['path'];
	if ($fileType == 'image'):
		$imgUrl = $this->AssetsImage->resize($path, 200, 300,
			array('adapter' => $this->data['AssetsAsset']['adapter'])
		);
	else:
		$imgUrl = $this->Html->image('/croogo/img/icons/' . $this->Filemanager->mimeTypeToImage($this->data['AssetsAttachment']['mime_type'])) . ' ' . $this->data['AssetsAttachment']['mime_type'];
	endif;
	echo $this->Html->beginBox(__d('croogo', 'Preview')) .
		$this->Html->link($imgUrl, $this->data['AssetsAsset']['path'], array(
			'class' => 'thickbox',
		));
	echo $this->Html->endBox();

$this->end();

$this->append('form-end', $this->Form->end());