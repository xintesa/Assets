<?php

App::uses('ImageHelper', 'Croogo.View/Helper');

class AssetsImageHelper extends ImageHelper {

	public function resize($path, $width, $height, $options = array(), $htmlAttributes = array(), $return = false) {
		$filename = basename($path);
		$uploadsDir = dirname(basename($path));
		if ($uploadsDir === '.') {
			$uploadsDir = '';
		}
		$cacheDir = dirname($path);
		$options = Hash::merge(array(
			'aspect' => true,
			'adapter' => false,
			'cacheDir' => $cacheDir,
			'uploadsDir' => $uploadsDir,
		), $options);
		$adapter = $options['adapter'];
		if ($adapter === 'LegacyLocalAttachment') {
			$options['resizedInd'] = 'resized';
		}
		$result = parent::resize($path, $width, $height, $options, $htmlAttributes, $return);
		$data = compact('result', 'path', 'width', 'height', 'aspect', 'htmlAttributes', 'adapter');
		Croogo::dispatchEvent('Assets.AssetsImageHelper.resize', $this->_View, $data);
		return $result;
	}
}
