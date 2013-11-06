<?php

$this->extend('/Common/admin_index');

$this->Html
	->addCrumb('', '/admin', array('icon' => 'home'))
	->addCrumb(__d('croogo', 'Attachments'), '/' . $this->request->url);

$this->start('actions');

echo $this->Croogo->adminAction(
	__d('croogo', 'New %s', __d('croogo', 'Attachment')),
	array('action' => 'add'),
	array('button' => 'success')
);

$this->end();
?>
<table class="table table-striped">
<?php

	$tableHeaders = $this->Html->tableHeaders(array(
		$this->Paginator->sort('id', __d('croogo', 'Id')),
		'&nbsp;',
		$this->Paginator->sort('title', __d('croogo', 'Title')),
		__d('croogo', 'URL'),
		__d('croogo', 'Actions'),
	));

?>
	<thead>
	<?php echo $tableHeaders; ?>
	</thead>
<?php

	$rows = array();
	foreach ($attachments as $attachment) {
		$actions = array();
		$actions[] = $this->Croogo->adminRowActions($attachment['AssetsAttachment']['id']);
		$actions[] = $this->Croogo->adminRowAction('',
			array('controller' => 'assets_attachments', 'action' => 'edit', $attachment['AssetsAttachment']['id']),
			array('icon' => 'pencil', 'tooltip' => __d('croogo', 'Edit this item'))
		);
		$actions[] = $this->Croogo->adminRowAction('',
			array('controller' => 'assets_attachments', 'action' => 'delete', $attachment['AssetsAttachment']['id']),
			array('icon' => 'trash', 'tooltip' => __d('croogo', 'Remove this item')),
			__d('croogo', 'Are you sure?'));

		$mimeType = explode('/', $attachment['AssetsAttachment']['mime_type']);
		$mimeType = $mimeType['0'];
		if ($mimeType == 'image') {
			$imgUrl = $this->AssetsImage->resize($attachment['AssetsAttachment']['path'], 100, 200, true, array('class' => 'img-polaroid', 'alt' => $attachment['AssetsAttachment']['title']));
			$thumbnail = $this->Html->link($imgUrl, $attachment['AssetsAttachment']['path'],
			array('escape' => false, 'class' => 'thickbox', 'title' => $attachment['AssetsAttachment']['title']));
		} else {
			$thumbnail = $this->Html->image('/croogo/img/icons/page_white.png', array('alt' => $attachment['AssetsAttachment']['mime_type'])) . ' ' . $attachment['AssetsAttachment']['mime_type'] . ' (' . $this->Assets->filename2ext($attachment['AssetsAttachment']['path']) . ')';
		}

		$actions = $this->Html->div('item-actions', implode(' ', $actions));

		$rows[] = array(
			$attachment['AssetsAttachment']['id'],
			$thumbnail,
			$attachment['AssetsAttachment']['title'],
			$this->Html->link(
				$this->Html->url($attachment['AssetsAttachment']['path'], true),
				$attachment['AssetsAttachment']['path'],
				array(
					'target' => '_blank',
				)
			),
			$actions,
		);
	}

	echo $this->Html->tableCells($rows);

?>
</table>
