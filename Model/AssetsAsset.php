<?php

App::uses('AssetsAppModel', 'Assets.Model');

class AssetsAsset extends AssetsAppModel {

	public $actsAs = array(
		'Croogo.Trackable',
	);

	public $useTable = 'assets';

}
