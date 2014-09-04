<?php

$this->extend('/Common/admin_edit');

$this->Html
	->addCrumb('', '/admin', array('icon' => 'home'))
	->addCrumb(__d('croogo', 'Attachments'), array('plugin' => 'assets', 'controller' => 'assets_attachments', 'action' => 'index'))
	->addCrumb(__d('croogo', 'Upload'), '/' . $this->request->url);

if ($this->layout === 'admin_popup'):
	$this->append('title', ' ');
endif;

$formUrl = array('plugin' => 'assets', 'controller' => 'assets_attachments', 'action' => 'add');
if (isset($this->params['named']['editor'])) {
	$formUrl['editor'] = 1;
}
$this->append('form-start', $this->Form->create('AssetsAttachment', array(
	'url' => $formUrl, 'type' => 'file',
)));

$model = isset($this->request->query['model']) ? $this->request->query['model'] : null;
$foreignKey = isset($this->request->query['foreign_key']) ? $this->request->query['foreign_key'] : null;

$this->append('tab-heading');
	echo $this->Croogo->adminTab(__d('croogo', 'Upload'), '#attachment-upload');
	echo $this->Croogo->adminTabs();
$this->end();

$this->append('tab-content');

	echo $this->Html->tabStart('attachment-upload');

		if (isset($model) && isset($foreignKey)):
			$assetUsage = 'AssetsAsset.AssetsAssetUsage.0.';
			echo $this->Form->input($assetUsage . 'model', array(
				'type' => 'hidden',
				'value' => $model,
			));
			echo $this->Form->input($assetUsage . 'foreign_key', array(
				'type' => 'hidden',
				'value' => $foreignKey,
			));
		endif;

		echo $this->Form->input('AssetsAsset.file', array('label' => __d('croogo', 'Upload'), 'type' => 'file'));

		if (isset($model) && isset($foreignKey)):
			echo $this->Form->input($assetUsage . 'featured_image', array(
				'type' => 'checkbox',
				'label' => 'Featured Image',
			));
		endif;

		echo $this->Form->input('AssetsAsset.adapter', array(
			'type' => 'select',
			'default' => 'LocalAttachment',
			'options' => StorageManager::configured(),
		));
		echo $this->Form->input('excerpt', array(
			'label' => __d('croogo', 'Caption'),
		));
		echo $this->Form->input('title');
		echo $this->Form->input('status', array(
			'type' => 'hidden', 'value' => true,
		));
		echo $this->Form->input('AssetsAsset.model', array(
			'type' => 'hidden',
			'value' => 'AssetsAttachment',
		));

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
		$this->Form->button(__d('croogo', 'Upload')) .
		$this->Form->end() .
		$this->Html->link(__d('croogo', 'Cancel'), $redirect, array(
			'button' => 'danger',
		));
	echo $this->Html->endBox();
	echo $this->Croogo->adminBoxes();
$this->end();

$this->append('form-end', $this->Form->end());

$script = "\$('[data-toggle=tab]:first').tab('show');";
$this->Js->buffer($script);