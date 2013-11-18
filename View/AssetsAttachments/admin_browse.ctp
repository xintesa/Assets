<style>
.popover-content { word-wrap: break-word; }
a i[class^=icon]:hover { text-decoration: none; }
</style>
<?php
$this->Html->script('Croogo.jquery/thickbox-compressed', array('inline' => false));
$this->Html->css('Croogo.thickbox', array('inline' => false));
?>
<div class="attachments index">

	<?php if ($this->layout != 'admin_popup'): ?>
	<h2><?php echo $title_for_layout; ?></h2>
	<?php endif; ?>

	<div class="row-fluid">
		<div class="span12 actions">
			<ul class="nav-buttons">
			<?php
				echo $this->Croogo->adminAction(
					__d('croogo', 'New Attachment'),
					array_merge(
						array('controller' => 'assets_attachments', 'action' => 'add', 'editor' => 1),
						array('?' => $this->request->query)
					)
				);
			?>
			</ul>
		</div>
	</div>

	<table class="table table-striped">
	<?php
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

		$query = array('?' => $this->request->query);
		$rows = array();
		foreach ($attachments as $attachment):
			$actions = array();
			if (isset($this->request->query['editor'])):
				$actions[] = $this->Html->link('', '#', array(
					'onclick' => "Croogo.Wysiwyg.choose('" . $attachment['AssetsAttachment']['slug'] . "');",
					'icon' => 'paper-clip',
					'tooltip' => __d('croogo', 'Insert')
				));
			endif;

			if (isset($this->request->query['asset_id'])):
				unset($query['?']['asset_id']);
				$addUrl = Hash::merge(array(
					'controller' => 'assets_asset_usages',
					'action' => 'add',
					'?' => array(
						'asset_id' => $attachment['AssetsAsset']['id'],
						'model' => $this->request->query['model'],
						'foreign_key' => $this->request->query['foreign_key'],
					)
				), $query);
				$actions[] = $this->Croogo->adminRowAction('', $addUrl, array(
					'icon' => 'plus',
					'method' => 'post',
				));
			else:
				$detailUrl = Hash::merge(array(
					'action' => 'browse',
					'?' => array(
						'asset_id' => $attachment['AssetsAsset']['id'],
					)
				), $query);
				$actions[] = $this->Html->link('', $detailUrl, array(
					'icon' => 'suitcase',
				));
			endif;

			$editUrl = Hash::merge($query, array(
				'controller' => 'assets_attachments',
				'action' => 'edit',
				$attachment['AssetsAttachment']['id'],
				'editor' => 1,
			));
			$actions[] = $this->Croogo->adminRowAction('', $editUrl,
				array('icon' => 'pencil', 'tooltip' => __d('croogo', 'Edit'))
			);

			$deleteUrl = Hash::merge($query, array(
				'controller' => 'assets_attachments',
				'action' => 'delete',
				$attachment['AssetsAttachment']['id'],
				'editor' => 1,
			));
			$actions[] = $this->Croogo->adminRowAction('', $deleteUrl, array(
				'icon' => 'trash',
				'tooltip' => __d('croogo', 'Delete')
				),
				__d('croogo', 'Are you sure?')
			);

			$mimeType = explode('/', $attachment['AssetsAsset']['mime_type']);
			$mimeType = $mimeType['0'];
			if ($mimeType == 'image') {
				$thumbnail = $this->Html->link($this->AssetsImage->resize($attachment['AssetsAsset']['path'], 100, 200, array(), array('class' => 'img-polaroid')), $attachment['AssetsAsset']['path'], array(
					'class' => 'thickbox',
					'escape' => false,
					'title' => $attachment['AssetsAttachment']['title'],
				));
			} else {
				$thumbnail = $this->Html->image('/croogo/img/icons/page_white.png') . ' ' . $attachment['AssetsAttachment']['mime_type'] . ' (' . $this->Filemanager->filename2ext($attachment['AssetsAttachment']['slug']) . ')';
				$thumbnail = $this->Html->link($thumbnail, '#', array(
					'escape' => false,
				));
			}

			$actions = $this->Html->div('item-actions', implode(' ', $actions));

			$url = $this->Html->link(
				Router::url($attachment['AssetsAsset']['path']),
				$attachment['AssetsAsset']['path'],
				array(
					'onclick' => "Croogo.Wysiwyg.choose('" . $attachment['AssetsAttachment']['slug'] . "');",
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
				'Size: ' .$this->Number->toReadableSize($attachment['AssetsAsset']['filesize'])
			);

			$rows[] = array(
				$attachment['AssetsAsset']['id'],
				$thumbnail,
				$title,
				$actions,
			);
		endforeach;

		echo $this->Html->tableCells($rows);
		echo $tableHeaders;
	?>
	</table>
</div>

<div class="row-fluid">
	<div class="span12">
		<div class="pagination">
		<ul>
			<?php echo $this->Paginator->first('< ' . __d('croogo', 'first')); ?>
			<?php echo $this->Paginator->prev('< ' . __d('croogo', 'prev')); ?>
			<?php echo $this->Paginator->numbers(); ?>
			<?php echo $this->Paginator->next(__d('croogo', 'next') . ' >'); ?>
			<?php echo $this->Paginator->last(__d('croogo', 'last') . ' >'); ?>
		</ul>
		</div>
		<div class="counter"><?php echo $this->Paginator->counter(array('format' => __d('croogo', 'Page %page% of %pages%, showing %current% records out of %count% total, starting on record %start%, ending on %end%'))); ?></div>
	</div>
</div>
<?php

$this->Js->buffer("$('.popovers').popover().on('click', function() { return false; });");
