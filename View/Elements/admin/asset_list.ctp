<?php $this->append('page-heading'); ?>
<style>
td .actions a:hover{
	text-decoration: none;
}
td .actions a.unregister-usage {
	color: #9D261D;
}
</style>
<?php
$this->end();

$this->Html->script('Assets.admin.js', array('block' => 'scriptBottom'));

$model = isset($model) ? $model : $this->Form->defaultModel;
$primaryKey = isset($primaryKey) ? $primaryKey : 'id';
$id = isset($foreignKey) ? $foreignKey : $this->data[$model][$primaryKey];

$detailUrl = array(
	'plugin' => 'assets',
	'controller' => 'assets_attachments',
	'action' => 'browse',
	'?' => array(
		'model' => $model,
		'foreign_key' => $id,
	),
);

$changeTypeUrl = array(
	'admin' => true,
	'plugin' => 'assets',
	'controller' => 'assets_asset_usages',
	'action' => 'change_type',
);

$assetListUrl = $this->Html->url(array(
	'admin' => true,
	'plugin' => 'assets',
	'controller' => 'assets_attachments',
	'action' => 'list',
	'?' => array(
		'model' => $model,
		'foreign_key' => $id,
	),
));

$unregisterUsageUrl = array(
	'admin' => true,
	'plugin' => 'assets',
	'controller' => 'assets_asset_usages',
	'action' => 'unregister',
);

if (!isset($attachments)):
	$Attachment = ClassRegistry::init('Assets.AssetsAttachment');
	$attachments = $Attachment->find('modelAttachments', array(
		'model' => $model,
		'foreign_key' => $id,
	));
endif;

$headers = array(
	__d('croogo', 'Preview'),
	__d('croogo', 'Type'),
	__d('croogo', 'Size'),
	__d('croogo', 'Actions'),
);

if (!$this->Helpers->loaded('AssetsImage')) {
	$this->AssetsImage = $this->Helpers->load('Assets.AssetsImage');
}

$rows = array();
foreach ($attachments as $attachment):
	$row = $action = array();
	$path = $attachment['AssetsAsset']['path'];
	list($mimeType, ) = explode('/', $attachment['AssetsAsset']['mime_type']);

	if ($mimeType === 'image'):
		$imgUrl = $this->AssetsImage->resize($path, 100, 200,
			array('adapter' => $attachment['AssetsAsset']['adapter']),
			array('alt' => $attachment['AssetsAttachment']['title'])
		);
		$thumbnail = $this->Html->link($imgUrl, $path,
			array('escape' => false, 'class' => 'thickbox', 'title' => $attachment['AssetsAttachment']['title'])
		);
	else:
		$imgUrl = $this->Html->image('/croogo/img/icons/page_white.png') . ' ' . $attachment['AssetsAsset']['filename'];
		$thumbnail = $this->Html->link($imgUrl,
			$attachment['AssetsAsset']['path'], array(
				'escape' => false,
				'target' => '_blank',
			)
		);
	endif;

	$preview = $this->Html->div(null, $thumbnail);
	if ($mimeType === 'image'):
		$preview .= $this->Html->div(null, sprintf(
			'<small>Shortcode: [image:%s]</small>', $attachment['AssetsAssetUsage']['id']
		));
		$preview .= $this->Html->tag('small', sprintf(
			'Dimension: %sx%s', $attachment['AssetsAsset']['width'], $attachment['AssetsAsset']['height']
		));
	endif;

	$detailUrl['?']['asset_id'] = $attachment['AssetsAsset']['id'];

	$typeCell = $this->Html->link($attachment['AssetsAssetUsage']['type'], 'javascript:void(0)', array(
		'class' => 'editable editable-click usage-type',
		'data-pk' => $attachment['AssetsAssetUsage']['id'],
		'data-url' => $this->Html->url($changeTypeUrl),
		'data-name' => 'type',
	));

	$row[] = $preview;
	$row[] = $typeCell;
	$row[] = $this->Number->toReadableSize($attachment['AssetsAsset']['filesize']);

	if ($mimeType === 'image'):
		$action[] = $this->Croogo->adminRowAction('', $detailUrl, array(
			'icon' => 'suitcase',
			'data-toggle' => 'browse',
			'tooltip' => __d('assets', 'View other sizes'),
		));

		$action[] = $this->Croogo->adminRowAction('', $changeTypeUrl, array(
			'icon' => 'star',
			'class' => 'change-usage-type',
			'data-pk' => $attachment['AssetsAssetUsage']['id'],
			'data-value' => 'FeaturedImage',
			'tooltip' => __d('assets', 'Set as FeaturedImage'),
		));

		$action[] = $this->Croogo->adminRowAction('', $unregisterUsageUrl, array(
			'icon' => 'trash',
			'class' => 'unregister-usage',
			'data-id' => $attachment['AssetsAssetUsage']['id'],
			'tooltip' => __d('assets', 'Unregister asset from this resource'),
		));
	else:
		$action[] = null;
	endif;
	$row[] = '<span class="actions">' . implode('&nbsp;', $action) . '</span>';
	$rows[] = $row;
endforeach;

$browseUrl = array_merge(
	Configure::read('Wysiwyg.attachmentBrowseUrl'),
	array(
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

$this->append('actions');
	echo $this->Croogo->adminAction(__d('assets', 'Reload'),
		$browseUrl,
		array(
			'icon' => 'refresh',
			'iconSize' => 'small',
			'data-toggle' => 'refresh',
		)
	);
	echo $this->Croogo->adminAction(__d('assets', 'Browse'),
		$browseUrl,
		array(
			'icon' => 'folder-open',
			'iconSize' => 'small',
			'data-toggle' => 'browse',
		)
	);
	echo $this->Croogo->adminAction(__d('assets', 'Upload'),
		$uploadUrl,
		array(
			'icon' => 'upload-alt',
			'iconSize' => 'small',
			'data-toggle' => 'browse',
		)
	);
$this->end();

?>
<div class="<?php echo $this->Layout->cssClass('row'); ?>">
	<div class="<?php echo $this->Layout->cssClass('fullColumn'); ?>">
		<table class="<?php echo $this->Layout->cssClass('tableClass'); ?> asset-list" data-url="<?php echo $assetListUrl; ?>">
			<thead><?php echo $this->Html->tableHeaders($headers); ?></thead>
			<tbody><?php echo $this->Html->tableCells($rows); ?></tbody>
		</table>
	</div>
</div>
<?php

$script =<<<EOF
	if (typeof $.fn.editable == 'function') {
		$('.editable').editable();
	} else {
		console.log('Note: bootstrap-xeditable plugin not found. Ensure your admin theme provides this plugin or use http://github.com/rchavik/AdminExtras as an alternative.');
	}
	tb_init('a.thickbox');
EOF;
if ($this->request->is('ajax')):
	echo $this->Html->scriptBlock($script);
else:
	$this->Js->buffer($script);
endif;
