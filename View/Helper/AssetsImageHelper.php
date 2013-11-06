<?php

App::uses('ImageHelper', 'Croogo.View/Helper');

class AssetsImageHelper extends ImageHelper {

	public function resize($path, $width, $height, $aspect = true, $htmlAttributes = array(), $return = false) {
		$result = parent::resize($path, $width, $height, $aspect, $htmlAttributes, $return);
		$data = compact('result', 'path', 'width', 'height', 'aspect', 'htmlAttributes');
		Croogo::dispatchEvent('Assets.AssetsImageHelper.resize', $this->_View, $data);
		return $result;
	}
}
