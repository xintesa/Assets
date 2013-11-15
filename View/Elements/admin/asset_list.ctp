<?php

$this->Html->script('Assets.admin.js', array('inline' => false));

$model = $this->Form->defaultModel;
$primaryKey = isset($primaryKey) ? $primaryKey : 'id';
$id = $this->data[$model][$primaryKey];

$Asset = ClassRegistry::init('Assets.AssetsAssetUsage');
$assets = $Asset->find('modelAssets', array(
	'model' => $model,
	'foreign_key' => $id,
));

$headers = array(
	__d('croogo', 'Preview'),
	__d('croogo', 'Type'),
	__d('croogo', 'Size'),
);

if (!$this->Helpers->loaded('AssetsImage')) {
	$this->AssetsImage = $this->Helpers->load('Assets.AssetsImage');
}

$rows = array();
foreach ($assets as $asset):
	$row = $action = array();
	$path = $asset['AssetsAsset']['path'];
	$imgUrl = $this->AssetsImage->resize($path, 100, 200,
		array('adapter' => $asset['AssetsAsset']['adapter']),
		array('class' => 'img-polaroid', 'alt' => $asset['AssetsAttachment']['title'])
	);
	$thumbnail = $this->Html->link($imgUrl, $path,
		array('escape' => false, 'class' => 'thickbox', 'title' => $asset['AssetsAttachment']['title'])
	);


	$row[] = $thumbnail;
	$row[] = $asset['AssetsAssetUsage']['type'];
	$row[] = $this->Number->toReadableSize($asset['AssetsAsset']['filesize']);

	$action[] = $this->Croogo->adminRowAction('Hello', '#');
	$row[] = implode(' ', $action);
	$rows[] = $row;
endforeach;

$browseUrl = array_merge(
	Configure::read('Wysiwyg.attachmentBrowseUrl'),
	array(
		'controller' => 'assets_assets',
		'?' => array('model' => $model, 'foreign_key' => $id),
	)
);

$uploadUrl = array(
	'admin' => true,
	'plugin' => 'assets',
	'controller' => 'assets_attachments',
	'action' => 'add',
	'editor' => true,
	'?' => array(
		'model' => $model,
		'foreign_key' => $id,
	),
);

?>
<div class="row-fluid">
	<div class="span12">
		<div class="actions pull-right">
			<ul class="nav-buttons">
			<?php
				echo $this->Croogo->adminAction(__d('assets', 'Browse'),
					$browseUrl,
					array(
						'icon' => 'folder-open',
						'iconSize' => 'small',
						'rel' => 'browse',
					)
				);
				echo $this->Croogo->adminAction(__d('assets', 'Upload'),
					$uploadUrl,
					array(
						'icon' => 'upload-alt',
						'iconSize' => 'small',
						'rel' => 'browse',
					)
				);
			?>
			</ul>
		</div>
	</div>
</div>
<div class="row-fluid">
	<div class="span12">
		<table class="table">
			<thead><?php echo $this->Html->tableHeaders($headers); ?></thead>
			<tbody><?php echo $this->Html->tableCells($rows); ?></tbody>
		</table>
	</div>
</div>
