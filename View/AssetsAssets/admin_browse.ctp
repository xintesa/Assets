<style>
.popover-content { word-wrap: break-word; }
a i[class^=icon]:hover { text-decoration: none; }
</style>
<div class="attachments index">

	<h2><?php echo $title_for_layout; ?></h2>

	<div class="row-fluid">
		<div class="span12 actions">
			<ul class="nav-buttons">
			<?php
				echo $this->Croogo->adminAction(
					__d('croogo', 'New Attachment'),
					array('action' => 'add', 'editor' => 1)
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
			$this->Paginator->sort('title', __d('croogo', 'Title')),
			$this->Paginator->sort('filename', __d('croogo', 'Filename')),
			$this->Paginator->sort('filesize', __d('croogo', 'Size')),
			__d('croogo', 'Actions'),
		));
		echo $tableHeaders;

		$rows = array();
		foreach ($attachments as $attachment):
			$actions = array();
			$actions[] = $this->Html->link('', '#', array(
				'onclick' => "Croogo.Wysiwyg.choose('" . $attachment['AssetsAttachment']['slug'] . "');",
				'icon' => 'paper-clip',
				'tooltip' => __d('croogo', 'Insert')
			));
			$actions[] = $this->Croogo->adminRowAction('',
				array('controller' => 'attachments', 'action' => 'edit', $attachment['AssetsAttachment']['id'], 'editor' => 1),
				array('icon' => 'pencil', 'tooltip' => __d('croogo', 'Edit'))
			);
			$actions[] = $this->Croogo->adminRowAction('', array(
				'controller' => 'attachments',
				'action' => 'delete',
				$attachment['AssetsAttachment']['id'],
				'editor' => 1,
			), array('icon' => 'trash', 'tooltip' => __d('croogo', 'Delete')), __d('croogo', 'Are you sure?'));

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
				'data-title' => __d('croogo', 'URL'),
				'data-html' => true,
				'data-placement' => 'top',
				'data-content' => $url,
			));

			$rows[] = array(
				$attachment['AssetsAsset']['id'],
				$thumbnail,
				$attachment['AssetsAttachment']['title'],
				$attachment['AssetsAsset']['filename'] . '&nbsp;' . $urlPopover,
				$this->Number->toReadableSize($attachment['AssetsAsset']['filesize']),
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
