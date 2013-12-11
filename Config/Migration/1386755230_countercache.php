<?php

class CounterCache extends CakeMigration {

	public $description = '';

	public $migration = array(
		'up' => array(
			'create_field' => array(
				'attachments' => array(
					'asset_count' => array('type' => 'integer', 'null' => true),
				),
			),
		),
		'down' => array(
			'drop_field' => array(
				'attachments' => array(
					'asset_count',
				),
			),
		)
	);

	public function before($direction) {
		return true;
	}

	public function after($direction) {
		return true;
	}

}
