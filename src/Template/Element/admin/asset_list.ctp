<?php

use Cake\Core\Configure;
use Cake\Utility\Inflector;
use Cake\ORM\TableRegistry;

$this->Html->script('Assets.admin.js', array('block' => 'scriptBottom'));

$model = isset($model) ? $model : $this->Form->defaultModel;
$primaryKey = isset($primaryKey) ? $primaryKey : 'id';

$data = ${Inflector::variable(Inflector::singularize($this->request->param('controller')))};

$id = isset($foreignKey) ? $foreignKey : $data->get($primaryKey);

$detailUrl = array(
	'prefix' => 'admin',
	'plugin' => 'Xintesa/Assets',
	'controller' => 'attachments',
	'action' => 'browse',
	'?' => array(
		'model' => $model,
		'foreign_key' => $id,
	),
);

$changeTypeUrl = array(
	'prefix' => 'admin',
	'plugin' => 'Xintesa/assets',
	'controller' => 'AssetUsages',
	'action' => 'change_type',
);

$assetListUrl = $this->Url->build(array(
	'prefix' => 'admin',
	'plugin' => 'Xintesa/Assets',
	'controller' => 'Attachments',
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
	$Attachment = TableRegistry::get('Xintesa/Assets.Attachments');
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

if (!$this->helpers()->loaded('AssetsImage')) {
	$this->loadHelper('Xintesa/Assets.AssetsImage');
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
		'data-url' => $this->Url->build($changeTypeUrl),
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
			'icon' => 'delete',
			'class' => 'unregister-usage red',
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
	'prefix' => 'admin',
	'plugin' => 'Xintesa/Assets',
	'controller' => 'Attachments',
	'action' => 'add',
	'editor' => true,
	'?' => array(
		'model' => $model,
		'foreign_key' => $id,
	),
);

$this->append('actions');
	echo '<div class="btn-group">';
	echo $this->Html->link(__d('assets', 'Reload'),
		$browseUrl,
		array(
			'div' => false,
			'icon' => 'refresh',
			'iconSize' => 'small',
			'data-toggle' => 'refresh',
			'button' => 'default',
			'tooltip' => __d('assets', 'Reload asset list for this content'),
		)
	);
	echo $this->Html->link(__d('assets', 'Browse'),
		$browseUrl,
		array(
			'div' => false,
			'icon' => 'folder-open',
			'iconSize' => 'small',
			'data-toggle' => 'browse',
			'button' => 'default',
			'tooltip' => __d('assets', 'Browse available assets'),
		)
	);
	echo $this->Html->link(__d('assets', 'Upload'),
		$uploadUrl,
		array(
			'div' => false,
			'icon' => 'upload',
			'iconSize' => 'small',
			'data-toggle' => 'browse',
			'button' => 'default',
			'tooltip' => __d('assets', 'Upload new asset for this content'),
		)
	);
	echo "</div>";
$this->end();

?>
<div class="<?php echo $this->Theme->getCssClass('row'); ?>">
	<div class="<?php echo $this->Theme->getCssClass('fullColumn'); ?>">
		<table class="<?php echo $this->Theme->getCssClass('tableClass'); ?> asset-list" data-url="<?php echo $assetListUrl; ?>">
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
//	tb_init('a.thickbox');
EOF;
if ($this->request->is('ajax')):
	echo $this->Html->scriptBlock($script);
else:
	$this->Js->buffer($script);
endif;
