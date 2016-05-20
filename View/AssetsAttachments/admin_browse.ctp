<?php

$this->extend('/Common/admin_index');

$this->append('page-heading');
?>
<style>
.popover-content { word-wrap: break-word; }
a i[class^=icon]:hover { text-decoration: none; }
</style>
<?php
$this->end();

$this->Html->script('Assets.admin', array('block' => 'scriptBottom'));
$this->Html->script('Croogo.jquery/thickbox-compressed', array('block' => 'scriptBottom'));
$this->Html->css('Croogo.thickbox', array('inline' => false));

$model = $foreignKey = $assetId = $filter = $filename = $type = $all = null;
if (!empty($this->request->query['model'])):
	$model = $this->request->query['model'];
endif;
if (!empty($this->request->query['foreign_key'])):
	$foreignKey = $this->request->query['foreign_key'];
endif;
if (!empty($this->request->query['asset_id'])):
	$assetId = $this->request->query['asset_id'];
endif;
if (!empty($this->request->query['type'])):
	$type = $this->request->query['type'];
endif;
if (!empty($this->request->query['filter'])):
	$filter = $this->request->query['filter'];
endif;
if (!empty($this->request->query['filename'])):
	$filename = $this->request->query['filename'];
endif;
if (!empty($this->request->query['all'])):
	$all = $this->request->query['all'];
endif;

$extractPath = "AssetsAsset.AssetsAssetUsage.{n}[model=$model][foreign_key=$foreignKey]";
?>

	<?php if ($this->layout != 'admin_popup'): ?>
	<h2><?php echo $title_for_layout; ?></h2>
	<?php endif; ?>

<?php
$this->append('actions');
	echo $this->Croogo->adminAction(
		__d('croogo', 'New Attachment'),
		array_merge(
			array('controller' => 'assets_attachments', 'action' => 'add', 'editor' => 1),
			array('?' => $this->request->query)
		)
	);

	$listUrl = array(
		'controller' => 'assets_attachments',
		'action' => 'browse',
		'?' => array(
			'model' => $model,
			'foreign_key' => $foreignKey,
		),
	);

	if (!$all):
		$listUrl['?']['all'] = true;
		$listUrl['?'] = array_merge($listUrl['?'], $this->request->query);
		$listTitle = __d('assets', 'List All Attachments');
	else:
		$listTitle = __d('assets', 'List Attachments');
	endif;
	echo $this->Croogo->adminAction($listTitle, $listUrl, array(
		'button' => 'success',
	));
$this->end();

$this->append('search');
	$filters = $this->Form->create('AssetsAttachment');
	$filters .= $this->Form->input('filter', array(
		'label' => false,
		'placeholder' => true,
		'div' => 'input text span4',
	));
	$filters .= $this->Form->input('filename', array(
		'label' => false,
		'placeholder' => true,
		'div' => 'input text span4',
	));
	$filters .= $this->Form->submit(__d('croogo', 'Filter'), array(
		'div' => 'input submit span2',
	));
	$filters .= $this->Form->end();
	$filterRow = sprintf('<div class="clearfix filter">%s</div>', $filters);

$this->end();

$this->append('table-heading');
	$tableHeaders = $this->Html->tableHeaders(array(
		$this->Paginator->sort('AssetsAsset.id', __d('croogo', 'Id')),
		'&nbsp;',
		$this->Paginator->sort('title', __d('croogo', 'Title')) . ' ' .
		$this->Paginator->sort('filename', __d('croogo', 'Filename')) . ' ' .
		$this->Paginator->sort('width', __d('assets', 'Width')) . ' ' .
		$this->Paginator->sort('height', __d('assets', 'Height')) . ' ' .
		$this->Paginator->sort('filesize', __d('croogo', 'Size')),
		__d('croogo', 'Actions'),
	));
	echo $tableHeaders;
$this->end();

$this->append('table-body');
	$query = array('?' => $this->request->query);
	$rows = array();
	foreach ($attachments as $attachment):
		$actions = array();
		$mimeType = explode('/', $attachment['AssetsAsset']['mime_type']);
		$mimeType = $mimeType['0'];

		if (isset($this->request->query['editor'])):
			if ($this->request->query['func']) {
				$jActions = $this->request->query['func'];
				$actions[] = $this->Html->link('', '#', array(
					'onclick' => $jActions . "('" . $attachment['AssetsAsset']['path'] . "');",
					'icon' => 'attach',
					'tooltip' => __d('croogo', 'Insert')
				));
			} else {
				$actions[] = $this->Html->link('', '#', array(
					'onclick' => "Croogo.Wysiwyg.choose('" . $attachment['AssetsAttachment']['slug'] . "');",
					'icon' => 'attach',
					'tooltip' => __d('croogo', 'Insert')
				));
			}
		endif;

		$deleteUrl = Hash::merge($query, array(
			'controller' => 'assets_attachments',
			'action' => 'delete',
			$attachment['AssetsAttachment']['id'],
			'editor' => 1,
		));

		$deleteAssetUrl = Hash::merge($query, array(
			'controller' => 'assets_assets',
			'action' => 'delete',
			$attachment['AssetsAsset']['id'],
		));

		$resizeUrl = array_merge(
			array('action' => 'resize', $attachment['AssetsAttachment']['id'], 'ext' => 'json'),
			array('?' => $query)
		);

		if (!isset($this->request->query['all']) &&
			!isset($this->request->query['asset_id'])
		) {
			$actions[] = $this->Croogo->adminRowAction('', $deleteUrl, array(
				'icon' => $this->Theme->getIcon('delete'),
				'tooltip' => __d('croogo', 'Delete Attachment')
				),
				__d('croogo', 'Are you sure?')
			);
		} elseif (isset($this->request->query['manage']) &&
			isset($this->request->query['asset_id'])
		) {
			$actions[] = $this->Croogo->adminRowAction('', $deleteAssetUrl, array(
				'icon' => 'delete',
				'icon' => $this->Theme->getIcon('delete'),
				'tooltip' => __d('croogo', 'Delete Attachment version')
				),
				__d('croogo', 'Are you sure?')
			);
		}

		if ($mimeType === 'image' &&
			$attachment['AssetsAttachment']['hash'] == $attachment['AssetsAsset']['hash']
		) {
			$resizeUrl = array_merge(
				array('action' => 'resize', $attachment['AssetsAttachment']['id'], 'ext' => 'json'),
				array('?' => $query)
			);
			$actions[] = $this->Croogo->adminRowAction('', $resizeUrl, array(
				'icon' => $this->Theme->getIcon('resize'),
				'tooltip' => __d('croogo', 'Resize this item'),
				'data-toggle' => 'resize-asset'
			));
		}

		if (isset($this->request->query['asset_id']) ||
			isset($this->request->query['all'])
		):
			unset($query['?']['asset_id']);

			$usage = Hash::extract($attachment, $extractPath);
			if (empty($usage) && $model !== 'AssetsAttachment'):
				$addUrl = Hash::merge(array(
					'controller' => 'assets_asset_usages',
					'action' => 'add',
					'?' => array(
						'asset_id' => $attachment['AssetsAsset']['id'],
						'model' => $model,
						'foreign_key' => $foreignKey,
					)
				), $query);
				$actions[] = $this->Croogo->adminRowAction('', $addUrl, array(
					'icon' => 'create',
					'method' => 'post',
				));
			endif;
		elseif ($mimeType === 'image'):
			$detailUrl = Hash::merge(array(
				'action' => 'browse',
				'?' => array(
					'asset_id' => $attachment['AssetsAsset']['id'],
				)
			), $query);
			$actions[] = $this->Html->link('', $detailUrl, array(
				'icon' => 'suitcase',
				'tooltip' => __d('assets', 'View other sizes'),
			));
		endif;

		if ($mimeType == 'image') {
			$img = $this->AssetsImage->resize(
				$attachment['AssetsAsset']['path'], 100, 200,
				array('adapter' => $attachment['AssetsAsset']['adapter'])
			);
			$thumbnail = $this->Html->link($img,
				$attachment['AssetsAsset']['path'],
				array(
					'class' => 'thickbox',
					'escape' => false,
					'title' => $attachment['AssetsAttachment']['title'],
				)
			);
			if (!empty($attachment['AssetsAssetUsage']['type']) &&
				$attachment['AssetsAssetUsage']['foreign_key'] === $foreignKey &&
				$attachment['AssetsAssetUsage']['model'] === $model
			):
				$thumbnail .= $this->Html->div(null,
					$this->Html->link(
						$this->Html->tag('span',
							$attachment['AssetsAssetUsage']['type'],
							array('class' => 'badge badge-info')
						),
						array(
							'action' => 'browse',
							'?' => array(
								'type' => $attachment['AssetsAssetUsage']['type']
							) + $this->request->query,
						),
						array(
							'escape' => false,
						)
					)
				);
			endif;
		} else {
			$thumbnail = $this->Html->image('/croogo/img/icons/page_white.png') . ' ' . $attachment['AssetsAsset']['mime_type'] . ' (' . $this->Filemanager->filename2ext($attachment['AssetsAttachment']['slug']) . ')';
			$thumbnail = $this->Html->link($thumbnail, '#', array(
				'escape' => false,
			));
		}

		$actions = $this->Html->div('item-actions', implode(' ', $actions));

		$url = $this->Html->link(
			Router::url($attachment['AssetsAsset']['path']),
			$attachment['AssetsAsset']['path'],
			array(
				'onclick' => "Croogo.Wysiwyg.choose('" . $attachment['AssetsAsset']['path'] . "'); return false;",
				'target' => '_blank',
			)
		);
		$urlPopover = $this->Croogo->adminRowAction('', '#', array(
			'class' => 'popovers',
			'icon' => 'link',
			'iconSize' => 'small',
			'data-title' => __d('croogo', 'URL'),
			'data-html' => true,
			'data-placement' => 'top',
			'data-content' => $url,
		));

		$title = $this->Html->para(null, $attachment['AssetsAttachment']['title']);
		$title .= $this->Html->para(null,
			$this->Text->truncate(
				$attachment['AssetsAsset']['filename'], 30
			) . '&nbsp;' . $urlPopover,
			array('title' => $attachment['AssetsAsset']['filename'])
		);

		$title .= $this->Html->para(null, 'Dimension: ' .
			$attachment['AssetsAsset']['width'] . ' x ' .
			$attachment['AssetsAsset']['height']
		);

		$title .= $this->Html->para(null,
			'Size: ' . $this->Number->toReadableSize($attachment['AssetsAsset']['filesize'])
		);

		if (empty($this->request->query['all']) && empty($this->request->query['asset_id'])) {
			$title .= $this->Html->para(null,
				'Number of versions: ' . $attachment['AssetsAttachment']['asset_count']
			);
		}

		$rows[] = array(
			$attachment['AssetsAsset']['id'],
			$thumbnail,
			$title,
			$actions,
		);
	endforeach;

	echo $this->Html->tableCells($rows);
$this->end();
$this->set('tableFooter', $tableHeaders);

$this->Js->buffer("$('.popovers').popover().on('click', function() { return false; });");
