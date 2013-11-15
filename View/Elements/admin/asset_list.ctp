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

$rows = array();
foreach ($assets as $asset):
	$row = $action = array();
	$row[] = $this->Html->image($asset['AssetsAsset']['path'],
	array('width' => 100, 'class' => 'img-polaroid')
	);
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

$this->append('actions');
echo $this->Croogo->adminAction(__d('croogo', 'Attachments'), $browseUrl,
	array('rel' => 'browse')
);
$this->end();

?>
<div class="row-fluid">
	<div class="span12">
		<table class="table">
			<thead><?php echo $this->Html->tableHeaders($headers); ?></thead>
			<tbody><?php echo $this->Html->tableCells($rows); ?></tbody>
		</table>
	</div>
</div>
