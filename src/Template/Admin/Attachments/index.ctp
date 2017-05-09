<?php

$this->Croogo->adminScript('Xintesa/Assets.admin');

$this->extend('Croogo/Core./Common/admin_index');

$this->Breadcrumbs
	->add(__d('croogo', 'Attachments'), $this->request->getUri()->getPath());

if (!empty($this->request->query)) {
	$query = $this->request->query;
} else {
	$query = array();
}

$this->append('action-buttons');

echo $this->Croogo->adminAction(
	__d('croogo', 'New ' . __d('croogo', 'Attachment')),
	array_merge(array('?' => $query), array('action' => 'add')),
	array('button' => 'success')
);

$this->end();

$detailUrl = array(
	'plugin' => 'Xintesa/Assets',
	'controller' => 'Attachments',
	'action' => 'browse',
	'?' => array(
		'manage' => true,
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

		$mimeType = explode('/', $attachment->asset->mime_type);
		$mimeType = $mimeType['0'];
		$assetCount = $attachment->asset_count . '&nbsp;';
		if ($mimeType == 'image') {
			$detailUrl['?']['id'] = $attachment->id;
			$actions[] = $this->Croogo->adminRowAction('', $detailUrl, array(
				'icon' => 'suitcase',
				'data-toggle' => 'browse',
				'tooltip' => __d('assets', 'View other sizes'),
			));

			$actions[] = $this->Croogo->adminRowActions($attachment->id);
			$resizeUrl = array_merge(
				array(
					'action' => 'resize',
					$attachment->id,
					'_ext' => 'json'
				),
				array('?' => $query)
			);
		}

		if (isset($resizeUrl)) {
			$actions[] = $this->Croogo->adminRowAction('', $resizeUrl, array(
				'icon' => $this->Theme->getIcon('resize'),
				'tooltip' => __d('croogo', 'Resize this item'),
				'data-toggle' => 'resize-asset'
			));
		}

		$editUrl = array_merge(
			array('action' => 'edit', $attachment->id),
			array('?' => $query)
		);
		$actions[] = $this->Croogo->adminRowAction('', $editUrl, array(
			'icon' => 'update',
			'tooltip' => __d('croogo', 'Edit this item'),
		));
		$deleteUrl = array('action' => 'delete', $attachment->id);
		$deleteUrl = array_merge(array('?' => $query), $deleteUrl);
		$actions[] = $this->Croogo->adminRowAction('', $deleteUrl, array(
			'icon' => 'delete',
			'tooltip' => __d('croogo', 'Remove this item'),
			'escapeTitle' => false,
		), __d('croogo', 'Are you sure?'));

		$path = $attachment->asset->path;
		if ($mimeType == 'image') {

			$imgUrl = $this->AssetsImage->resize($path, 100, 200,
				array('adapter' => $attachment->asset->adapter),
				array('alt' => $attachment->title)
			);
			$thumbnail = $this->Html->link($imgUrl, $path, [
				'escape' => false,
				'data-toggle' => 'lightbox',
				'title' => $attachment['AssetsAttachment']['title'],
			]);
		} else {
			$thumbnail = $this->Html->image('Croogo/Core./img/icons/page_white.png', array('alt' => $mimeType)) . ' ' . $mimeType . ' (' . $this->Assets->filename2ext($attachment->asset->path) . ')';
		}

		$actions = $this->Html->div('item-actions', implode(' ', $actions));

		$rows[] = array(
			$attachment->id,
			$thumbnail,
			$this->Html->div(null, $attachment->title) . '&nbsp;' .
			$this->Html->link(
				$this->Url->build($path, true),
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