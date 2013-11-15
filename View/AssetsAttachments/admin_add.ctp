<?php

$this->extend('/Common/admin_edit');

$this->Html
	->addCrumb('', '/admin', array('icon' => 'home'))
	->addCrumb(__d('croogo', 'Attachments'), array('plugin' => 'assets', 'controller' => 'assets_attachments', 'action' => 'index'))
	->addCrumb(__d('croogo', 'Upload'), '/' . $this->request->url);

$formUrl = array('plugin' => 'assets', 'controller' => 'assets_attachments', 'action' => 'add');
if (isset($this->params['named']['editor'])) {
	$formUrl['editor'] = 1;
}
echo $this->Form->create('AssetsAttachment', array('url' => $formUrl, 'type' => 'file'));

$model = isset($this->request->query['model']) ? $this->request->query['model'] : null;
$foreignKey = isset($this->request->query['foreign_key']) ? $this->request->query['foreign_key'] : null;

?>
<div class="row-fluid">
	<div class="span8">

		<ul class="nav nav-tabs">
		<?php
			echo $this->Croogo->adminTab(__d('croogo', 'Upload'), '#attachment-upload');
		?>
		</ul>

		<div class="tab-content">

			<div id="attachment-upload" class="tab-pane">
			<?php
			$assetUsage = 'AssetsAsset.AssetsAssetUsage.0.';
			echo $this->Form->input($assetUsage . 'model', array(
				'type' => 'hidden',
				'value' => $model,
			));
			echo $this->Form->input($assetUsage . 'foreign_key', array(
				'type' => 'hidden',
				'value' => $foreignKey,
			));
			echo $this->Form->input('AssetsAsset.file', array('label' => __d('croogo', 'Upload'), 'type' => 'file'));
			echo $this->Form->input('AssetsAsset.adapter', array(
				'type' => 'select',
				'default' => 'LocalAttachment',
				'options' => StorageManager::configured(),
			));
			$this->Form->inputDefaults(array(
				'class' => 'span8',
			));
			echo $this->Form->input('excerpt', array('label' => __d('croogo', 'Caption')));
			echo $this->Form->input('title');
			echo $this->Form->input('status', array('type' => 'hidden', 'value' => true));
			echo $this->Form->input('AssetsAsset.model', array(
				'type' => 'hidden',
				'value' => 'AssetsAttachment',
			));
			?>
			</div>

			<?php echo $this->Croogo->adminTabs(); ?>
		</div>
	</div>

	<div class="span4">
	<?php
		$redirect = array('action' => 'index');
		if ($this->Session->check('Wysiwyg.redirect')) {
			$redirect = $this->Session->read('Wysiwyg.redirect');
		}
		echo $this->Html->beginBox(__d('croogo', 'Publishing')) .
			$this->Form->button(__d('croogo', 'Upload'), array('button' => 'default')) .
			$this->Form->end() .
			$this->Html->link(__d('croogo', 'Cancel'), $redirect, array('button' => 'danger')) .
			$this->Html->endBox();
		echo $this->Croogo->adminBoxes();
	?>
	</div>

</div>
<?php echo $this->Form->end(); ?>