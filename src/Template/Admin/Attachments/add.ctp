<?php

use Xintesa\Assets\Utility\StorageManager;

$this->extend('Croogo/Core./Common/admin_edit');

$this->Html->css(array(
	'Assets.jquery.fileupload',
), array(
	'inline' => false,
));
$this->Croogo->adminScript(array(
	'Xintesa/Assets.fileupload/vendor/jquery.ui.widget',
	'Xintesa/Assets.fileupload/tmpl.min.js',
	'Xintesa/Assets.fileupload/load-image.all.min',
	'Xintesa/Assets.fileupload/canvas-to-blob.min',
	'Xintesa/Assets.fileupload/jquery.iframe-transport',
	'Xintesa/Assets.fileupload/jquery.fileupload',
	'Xintesa/Assets.fileupload/jquery.fileupload-process',
	'Xintesa/Assets.fileupload/jquery.fileupload-image',
	'Xintesa/Assets.fileupload/jquery.fileupload-audio',
	'Xintesa/Assets.fileupload/jquery.fileupload-video',
	'Xintesa/Assets.fileupload/jquery.fileupload-validate',
	'Xintesa/Assets.fileupload/jquery.fileupload-ui',
));

$this->Html
	->addCrumb(__d('croogo', 'Attachments'), [
		'plugin' => 'Xintesa/Assets',
		'controller' => 'Attachments',
		'action' => 'index'
	])
	->addCrumb(__d('croogo', 'Upload'), '/' . $this->request->url);

if ($this->layout === 'admin_popup'):
	$this->append('title', ' ');
endif;

$formUrl = ['plugin' => 'Xintesa/Assets', 'controller' => 'Attachments', 'action' => 'add'];
if ($this->request->query('editor')) {
	$formUrl['editor'] = 1;
}
$this->append('form-start', $this->Form->create('Attachments', array(
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

		echo $this->element('Xintesa/Assets.admin/fileupload');

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
	if ($this->request->session()->check('Wysiwyg.redirect')) {
		$redirect = $this->request->session()->read('Wysiwyg.redirect');
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

$editorMode = isset($formUrl['editor']) ? $formUrl['editor'] : 0;
$xhrUploadUrl = $this->Url->build($formUrl);
$script =<<<EOF

	\$('[data-toggle=tab]:first').tab('show');
	var filesToUpload = [];
	var uploadContext = [];
	var \$form = \$('#AssetsAttachmentAdminAddForm');
	\$form.fileupload({
		url: '$xhrUploadUrl',
		add: function(e, data) {
			var that = this;
			$.blueimp.fileupload.prototype.options.add.call(that, e, data)
			filesToUpload.push(data.files[0]);
			uploadContext.push(data.context);
		}
	});
	\$('#start_upload').one('click', function(e) {
		for (var i in filesToUpload) {
			\$form.fileupload('send', {
				files: [filesToUpload[i]],
				context: uploadContext[i]
			});
		}
		e.preventDefault();
		if (filesToUpload.length > 0 && $editorMode == 1) {
			$(this).text('Close').one('click', function(e) {
				window.close();
			});
		}
	});
EOF;

$this->Js->buffer($script);