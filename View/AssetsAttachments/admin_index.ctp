<?php

$this->Html->script('Assets.admin', array('inline' => false));

$this->extend('/Common/admin_index');

$this->Html
	->addCrumb('', '/admin', array('icon' => 'home'))
	->addCrumb(__d('croogo', 'Attachments'), '/' . $this->request->url);

if (!empty($this->request->query)) {
	$query = $this->request->query;
} else {
	$query = array();
}

$this->start('actions');

echo $this->Croogo->adminAction(
	__d('croogo', 'New %s', __d('croogo', 'Attachment')),
	array_merge(array('?' => $query), array('action' => 'add')),
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
		$resizeUrl = array_merge(
			array('action' => 'resize', $attachment['AssetsAttachment']['id'], 'ext' => 'json'),
			array('?' => $query)
		);
		$actions[] = $this->Croogo->adminRowAction('', $resizeUrl,
			array('icon' => 'resize-small', 'tooltip' => __d('croogo', 'Resize this item'), 'data-toggle' => 'resize-asset')
		);
		$editUrl = array_merge(
			array('action' => 'edit', $attachment['AssetsAttachment']['id']),
			array('?' => $query)
		);
		$actions[] = $this->Croogo->adminRowAction('', $editUrl,
			array('icon' => 'pencil', 'tooltip' => __d('croogo', 'Edit this item'))
		);
		$deleteUrl = array('action' => 'delete', $attachment['AssetsAttachment']['id']);
		$deleteUrl = array_merge(array('?' => $query), $deleteUrl);
		$actions[] = $this->Croogo->adminRowAction('', $deleteUrl,
			array('icon' => 'trash', 'tooltip' => __d('croogo', 'Remove this item')),
			__d('croogo', 'Are you sure?'));

		$mimeType = explode('/', $attachment['AssetsAsset']['mime_type']);
		$mimeType = $mimeType['0'];
		$path = $attachment['AssetsAsset']['path'];
		if ($mimeType == 'image') {
			$imgUrl = $this->AssetsImage->resize($path, 100, 200,
				array('adapter' => $attachment['AssetsAsset']['adapter']),
				array('class' => 'img-polaroid', 'alt' => $attachment['AssetsAttachment']['title'])
			);
			$thumbnail = $this->Html->link($imgUrl, $path,
				array('escape' => false, 'class' => 'thickbox', 'title' => $attachment['AssetsAttachment']['title'])
			);
		} else {
			$thumbnail = $this->Html->image('/croogo/img/icons/page_white.png', array('alt' => $mimeType)) . ' ' . $mimeType . ' (' . $this->Assets->filename2ext($attachment['AssetsAttachment']['path']) . ')';
		}

		$actions = $this->Html->div('item-actions', implode(' ', $actions));

		$rows[] = array(
			$attachment['AssetsAttachment']['id'],
			$thumbnail,
			$attachment['AssetsAttachment']['title'],
			$this->Html->link(
				$this->Html->url($path, true),
				$path,
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
