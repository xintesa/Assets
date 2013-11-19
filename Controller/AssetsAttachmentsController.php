<?php

App::uses('AssetsAppController', 'Assets.Controller');

/**
 * Attachments Controller
 *
 * This file will take care of file uploads (with rich text editor integration).
 *
 * @category Assets.Controller
 * @package  Assets.Controller
 * @author   Fahad Ibnay Heylaal <contact@fahad19.com>
 * @author   Rachman Chavik <contact@xintesa.com>
 * @license  http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link     http://www.croogo.org
 */
class AssetsAttachmentsController extends AssetsAppController {

/**
 * Models used by the Controller
 *
 * @var array
 * @access public
 */
	public $uses = array('Assets.AssetsAttachment');

/**
 * Helpers used by the Controller
 *
 * @var array
 * @access public
 */
	public $helpers = array('FileManager.FileManager', 'Text', 'Assets.AssetsImage');

	public $paginate = array(
		'paramType' => 'querystring',
		'limit' => 5,
	);

	public $components = array(
		'Search.Prg' => array(
			'presetForm' => array(
				'paramType' => 'querystring',
			),
			'commonProcess' => array(
				'paramType' => 'querystring',
				'filterEmpty' => 'true',
			),
		),
	);

	public $presetVars = true;


/**
 * Before executing controller actions
 *
 * @return void
 * @access public
 */
	public function beforeFilter() {
		parent::beforeFilter();

		if ($this->action == 'admin_add') {
			$this->Security->csrfCheck = false;
		}
	}

/**
 * Admin index
 *
 * @return void
 * @access public
 */
	public function admin_index() {
		$this->set('title_for_layout', __d('croogo', 'Attachments'));

		$this->Prg->commonProcess();

		if (empty($this->request->query)) {
			$this->AssetsAttachment->recursive = 0;
			$this->paginate['AssetsAttachment']['order'] = 'AssetsAttachment.created DESC';
		} else {
			if (isset($this->request->query['asset_id'])) {
				$this->paginate = array_merge(array('versions'), $this->paginate);
				$this->paginate['asset_id'] = $this->request->query['asset_id'];
			} else {
				$this->paginate = array_merge(array('modelAttachments'), $this->paginate);
			}
			if (isset($this->request->query['model'])) {
				$this->paginate['model'] = $this->request->query['model'];
			}
			if (isset($this->request->query['foreign_key'])) {
				$this->paginate['foreign_key'] = $this->request->query['foreign_key'];
			};

		}
		$this->set('attachments', $this->paginate($this->AssetsAttachment->parseCriteria($this->request->query)));
	}

/**
 * Admin add
 *
 * @return void
 * @access public
 */
	public function admin_add() {
		$this->set('title_for_layout', __d('croogo', 'Add Attachment'));

		if (isset($this->request->params['named']['editor'])) {
			$this->layout = 'admin_popup';
		}

		if ($this->request->is('post') || !empty($this->request->data)) {

			if (empty($this->data['AssetsAttachment'])) {
				$this->AssetsAttachment->invalidate('file', __d('croogo', 'Upload failed. Please ensure size does not exceed the server limit.'));
				return;
			}

			$this->AssetsAttachment->create();
			$saved = $this->AssetsAttachment->saveAll(
				$this->request->data,
				array('deep' => true)
			);

			if ($saved) {
				$this->Session->setFlash(__d('croogo', 'The Attachment has been saved'), 'default', array('class' => 'success'));
				$url = array();
				if (isset($this->request->data['AssetsAsset']['AssetsAssetUsage'][0])) {
					$usage = $this->request->data['AssetsAsset']['AssetsAssetUsage'][0];
					if (!empty($usage['model']) && !empty($usage['foreign_key'])) {
						$url['?']['model'] = $usage['model'];
						$url['?']['foreign_key'] = $usage['foreign_key'];
					}
				}
				if (isset($this->request->params['named']['editor'])) {
					$url = array_merge($url, array('action' => 'browse'));
				} else {
					$url = array_merge($url, array('action' => 'index'));
				}
				return $this->redirect($url);
			} else {
				$this->Session->setFlash(__d('croogo', 'The Attachment could not be saved. Please, try again.'), 'default', array('class' => 'error'));
			}
		}
	}

/**
 * Admin edit
 *
 * @param int $id
 * @return void
 * @access public
 */
	public function admin_edit($id = null) {
		$this->set('title_for_layout', __d('croogo', 'Edit Attachment'));

		if (isset($this->request->params['named']['editor'])) {
			$this->layout = 'admin_popup';
		}

		$redirect = array('action' => 'index');
		if (!empty($this->request->query)) {
			$redirect = array_merge(
				$redirect,
				array('action' => 'browse', '?' => $this->request->query)
			);
		}

		if (!$id && empty($this->request->data)) {
			$this->Session->setFlash(__d('croogo', 'Invalid Attachment'), 'default', array('class' => 'error'));
			return $this->redirect($redirect);
		}
		if (!empty($this->request->data)) {
			if ($this->AssetsAttachment->save($this->request->data)) {
				$this->Session->setFlash(__d('croogo', 'The Attachment has been saved'), 'default', array('class' => 'success'));
				return $this->redirect($redirect);
			} else {
				$this->Session->setFlash(__d('croogo', 'The Attachment could not be saved. Please, try again.'), 'default', array('class' => 'error'));
			}
		}
		if (empty($this->request->data)) {
			$this->request->data = $this->AssetsAttachment->read(null, $id);
		}
	}

/**
 * Admin delete
 *
 * @param int $id
 * @return void
 * @access public
 */
	public function admin_delete($id = null) {
		if (!$id) {
			$this->Session->setFlash(__d('croogo', 'Invalid id for Attachment'), 'default', array('class' => 'error'));
			return $this->redirect(array('action' => 'index'));
		}

		$redirect = array('action' => 'index');
		if (!empty($this->request->query)) {
			$redirect = array_merge(
				$redirect,
				array('action' => 'browse', '?' => $this->request->query)
			);
		}

		$this->AssetsAttachment->begin();
		if ($this->AssetsAttachment->delete($id)) {
			$this->AssetsAttachment->commit();
			$this->Session->setFlash(__d('croogo', 'Attachment deleted'), 'default', array('class' => 'success'));
			return $this->redirect($redirect);
		} else {
			$this->Session->setFlash(__d('croogo', 'Invalid id for Attachment'), 'default', array('class' => 'error'));
			return $this->redirect($redirect);
		}
	}

/**
 * Admin browse
 *
 * @return void
 * @access public
 */
	public function admin_browse() {
		$this->layout = 'admin_popup';
		$this->admin_index();
	}

}
