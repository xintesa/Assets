<?php

namespace Xintesa\Assets\Model\Table;

use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;


/**
 * AssetUsages Table
 *
 */
class AssetUsagesTable extends AssetsAppTable {

	public function initialize(array $config) {
		parent::initialize($config);
		$this->table('asset_usages');

		$this->belongsTo('Assets', [
			'className' => 'Xintesa/Assets.Assets',
			'foreignKey' => 'asset_id',
		]);

		$this->addBehavior('Croogo/Core.Trackable');
	}

	public function beforeSave(Event $event, EntityInterface $entity, ArrayObject $options) {
		if (!empty($entity->featured_image)) {
			$entity->type = 'FeaturedImage';
			$entity->unsetProperty('featured_image');
		}
		return true;
	}

}
