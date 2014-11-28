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

$this->append('actions');

echo $this->Croogo->adminAction(
	__d('croogo', 'New %s', __d('croogo', 'Attachment')),
	array_merge(array('?' => $query), array('action' => 'add')),
	array('button' => 'success')
);

$this->end();

$detailUrl = array(
	'plugin' => 'assets',
	'controller' => 'assets_attachments',
	'action' => 'browse',
	'?' => array(
		'manage' => true,
		'model' => 'AssetsAttachment',
		'foreign_key' => null,
		'asset_id' => null,
	),
);

$this->append('table-heading');
	$tableHeaders = $this->Html->tableHeaders(array(
		$this->Paginator->sort('id', __d('croogo', 'Id')),
		'&nbsp;',
		$this->Paginator->sort('title', __d('croogo', 'Title')),
		__d('croogo', 'Versions'),
		__d('croogo', 'Actions'),
	));

	echo $this->Html->tag('thead', $tableHeaders);
$this->end();

$this->append('table-body');
	$rows = array();
	foreach ($attachments as $attachment) {
		$actions = array();

		$mimeType = explode('/', $attachment['AssetsAsset']['mime_type']);
		$mimeType = $mimeType['0'];
		$assetCount = $attachment['AssetsAttachment']['asset_count'] . '&nbsp;';
		if ($mimeType == 'image') {
			$detailUrl['?']['foreign_key'] = $attachment['AssetsAttachment']['id'];
			$detailUrl['?']['asset_id'] = $attachment['AssetsAsset']['id'];
			$actions[] = $this->Croogo->adminRowAction('', $detailUrl, array(
				'icon' => 'suitcase',
				'data-toggle' => 'browse',
				'tooltip' => __d('assets', 'View other sizes'),
			));

			$actions[] = $this->Croogo->adminRowActions($attachment['AssetsAttachment']['id']);
			$resizeUrl = array_merge(
				array(
					'action' => 'resize',
					$attachment['AssetsAttachment']['id'],
					'ext' => 'json'
				),
				array('?' => $query)
			);
		}

		if (isset($resizeUrl)) {
			$actions[] = $this->Croogo->adminRowAction('', $resizeUrl, array(
				'icon' => 'resize',
				'tooltip' => __d('croogo', 'Resize this item'),
				'data-toggle' => 'resize-asset'
			));
		}

		$editUrl = array_merge(
			array('action' => 'edit', $attachment['AssetsAttachment']['id']),
			array('?' => $query)
		);
		$actions[] = $this->Croogo->adminRowAction('', $editUrl, array(
			'icon' => 'update',
			'tooltip' => __d('croogo', 'Edit this item'),
		));
		$deleteUrl = array('action' => 'delete', $attachment['AssetsAttachment']['id']);
		$deleteUrl = array_merge(array('?' => $query), $deleteUrl);
		$actions[] = $this->Croogo->adminRowAction('', $deleteUrl, array(
			'icon' => 'delete',
			'tooltip' => __d('croogo', 'Remove this item'),
			'escapeTitle' => false,
		), __d('croogo', 'Are you sure?'));

		$path = $attachment['AssetsAsset']['path'];
		if ($mimeType == 'image') {

			$imgUrl = $this->AssetsImage->resize($path, 100, 200,
				array('adapter' => $attachment['AssetsAsset']['adapter']),
				array('alt' => $attachment['AssetsAttachment']['title'])
			);
			$thumbnail = $this->Html->link($imgUrl, $path,
				array('escape' => false, 'class' => 'thickbox', 'title' => $attachment['AssetsAttachment']['title'])
			);
		} else {
			$thumbnail = $this->Html->image('/croogo/img/icons/page_white.png', array('alt' => $mimeType)) . ' ' . $mimeType . ' (' . $this->Assets->filename2ext($attachment['AssetsAsset']['path']) . ')';
		}

		$actions = $this->Html->div('item-actions', implode(' ', $actions));

		$rows[] = array(
			$attachment['AssetsAttachment']['id'],
			$thumbnail,
			$this->Html->div(null, $attachment['AssetsAttachment']['title']) . '&nbsp;' .
			$this->Html->link(
				$this->Html->url($path, true),
				$path,
				array(
					'target' => '_blank',
				)
			),
			$assetCount,
			$actions,
		);
	}

	echo $this->Html->tableCells($rows);
$this->end();
