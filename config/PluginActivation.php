<?php

namespace Xintesa\Assets\Config;

use Cake\ORM\TableRegistry;
use Cake\Cache\Cache;
use Croogo\Core\Plugin;

/**
 * Assets Activation
 *
 * Activation class for Assets plugin.
 *
 * @author   Rachman Chavik <contact@xintesa.com>
 * @license  http://www.opensource.org/licenses/mit-license.php The MIT License
 */
class PluginActivation {

/**
 * onActivate will be called if this returns true
 *
 * @param  object $controller Controller
 * @return boolean
 */
	public function beforeActivation(&$controller) {
		/*
		if (!Plugin::loaded('Imagine')) {
			$plugin = new Plugin();
			$plugin->addBootstrap('Imagine');
			Plugin::load('Imagine');
			Log::info('Imagine plugin added to bootstrap');
		}
		*/
		return true;
	}

/**
 * Creates the necessary settings
 *
 * @param object $controller Controller
 * @return void
 */
	public function onActivation(&$controller) {
		$CroogoPlugin = new Plugin();
		$result = $CroogoPlugin->migrate('Xintesa/Assets');
		if ($result) {
			$Settings = TableRegistry::get('Croogo/Settings.Settings');
			$Settings->write('Assets.installed', true);
			Cache::clearGroup('menus');
		}
		return $result;
	}

/**
 * onDeactivate will be called if this returns true
 *
 * @param  object $controller Controller
 * @return boolean
 */
	public function beforeDeactivation(&$controller) {
		return true;
	}

/**
 * onDeactivation
 *
 * @param object $controller Controller
 * @return void
 */
	public function onDeactivation(&$controller) {
		$Settings = TableRegistry::get('Croogo/Settings.Settings');
		$Settings->deleteKey('Assets.installed');
	}

}
