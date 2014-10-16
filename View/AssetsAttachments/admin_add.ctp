<?php

$this->extend('/Common/admin_edit');

$this->Html->css(array(
	'Assets.jquery.fileupload',
), array(
	'inline' => false,
));
$this->Croogo->adminScript(array(
	'Assets.fileupload/vendor/jquery.ui.widget',
	'Assets.fileupload/tmpl.min.js',
	'Assets.fileupload/load-image.all.min',
	'Assets.fileupload/canvas-to-blob.min',
	'Assets.fileupload/jquery.iframe-transport',
	'Assets.fileupload/jquery.fileupload',
	'Assets.fileupload/jquery.fileupload-process',
	'Assets.fileupload/jquery.fileupload-image',
	'Assets.fileupload/jquery.fileupload-audio',
	'Assets.fileupload/jquery.fileupload-video',
	'Assets.fileupload/jquery.fileupload-validate',
	'Assets.fileupload/jquery.fileupload-ui',
));

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

		echo $this->element('Assets.admin/fileupload');

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
		$this->Form->button(__d('croogo', 'Upload'), array(
			'icon' => 'upload',
			'button' => 'primary',
			'class' => 'start',
			'type' => 'submit',
			'id' => 'start_upload',
		)) .
		$this->Form->end() .
		$this->Html->link(__d('croogo', 'Cancel'), $redirect, array(
			'button' => 'danger',
		));
	echo $this->Html->endBox();
	echo $this->Croogo->adminBoxes();
$this->end();

$this->append('form-end', $this->Form->end());

$xhrUploadUrl = $this->Html->url($formUrl);
$script =<<<EOF

	\$('[data-toggle=tab]:first').tab('show');
	var filesToUpload = [];
	var \$form = \$('#AssetsAttachmentAdminAddForm');
	\$form.fileupload({
		url: '$xhrUploadUrl',
		add: function(e, data) {
			var that = this;
			filesToUpload.push(data.files[0]);
			$.blueimp.fileupload.prototype.options.add.call(that, e, data)
		}
	});
	\$('#start_upload').on('click', function(e) {
		for (var i in filesToUpload) {
			\$form.fileupload('send', { files: [filesToUpload[i]] });
		}
		e.preventDefault();
	});
EOF;

$this->Js->buffer($script);