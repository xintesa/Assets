<?php

/**
 * Assets Activation
 *
 * Activation class for Assets plugin.
 *
 * @author   Rachman Chavik <contact@xintesa.com>
 * @license  http://www.opensource.org/licenses/mit-license.php The MIT License
 */
class AssetsActivation {

/**
 * onActivate will be called if this returns true
 *
 * @param  object $controller Controller
 * @return boolean
 */
	public function beforeActivation(&$controller) {
		return true;
	}

/**
 * Creates the necessary settings
 *
 * @param object $controller Controller
 * @return void
 */
	public function onActivation(&$controller) {
		$CroogoPlugin = new CroogoPlugin();
		$result = $CroogoPlugin->migrate('Assets');
		if ($result) {
			$Setting = ClassRegistry::init('Settings.Setting');
			$Setting->write('Assets.installed', true);
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
		$Setting = ClassRegistry::init('Settings.Setting');
		$Setting->deleteKey('Assets.installed');
	}

}
