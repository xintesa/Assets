<?php

namespace Xintesa\Assets\Controller\Admin;

class AssetsController extends AppController {

	public $uses = array(
		'Assets.AssetsAsset',
	);

	public function delete($id = null) {
		if ($id) {
			$result = $this->AssetsAsset->delete($id);
		} else {
			throw new NotFoundException('Invalid Id');
		}
		if ($result) {
			$this->Session->setFlash('Asset has been deleted', 'flash', array('class' => 'success'));
		} else {
			$this->Session->setFlash('Unable to delete Asset', 'flash', array('class' => 'error'));
			$this->log($this->AssetsAsset->validationErrors);
		}
		return $this->redirect($this->referer());
	}

}
