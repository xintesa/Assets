<?php

namespace Xintesa\Assets\Controller\Admin;

use Cake\Event\Event;
use Cake\Log\Log;
use Croogo\Core\Croogo;
use Xintesa\Assets\Controller\Admin\AppController;

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
class AttachmentsController extends AppController {

/**
 * Helpers used by the Controller
 *
 * @var array
 * @access public
 */
	public $helpers = [
		'Croogo/FileManager.FileManager',
		'Text',
		'Xintesa/Assets.AssetsImage'
	];

	public $paginate = array(
		'paramType' => 'querystring',
		'limit' => 5,
	);

	public $presetVars = true;

	public function initialize() {
		parent::initialize();
		$this->loadComponent('Search.Prg', [
			'actions' => [
				'index', 'browse', 'listings',
			],
		]);
		$this->loadModel('Xintesa/Assets.Attachments');
	}

/**
 * Before executing controller actions
 *
 * @return void
 * @access public
 */
	public function beforeFilter(Event $event) {
		parent::beforeFilter($event);

		$noCsrfCheck = array('add', 'resize');
		if (in_array($this->request->action, $noCsrfCheck)) {
			$this->eventManager()->off($this->Csrf);
		}
		if ($this->request->action == 'resize') {
			$this->Security->config('validatePost', false);
		}
	}

/**
 * Admin index
 *
 * @return void
 * @access public
 */
	public function index() {
		$this->set('title_for_layout', __d('croogo', 'Attachments'));

		$query = $this->Attachments->find();

		$finder = 'modelAttachments';

		$isChooser = false;

		if ($this->request->query('links') || $this->request->query('chooser')) {
			$isChooser = true;
		}

		if ($this->request->query('manage')) {
			$finder = 'versions';
			$model = $this->request->query('model');
			$foreignKey = $this->request->query('foreign_key');
			$this->set(compact('model', 'foreignKey'));
			unset($this->request->query['model']);
			unset($this->request->query['foreign_key']);
		}

		if ($this->request->query('sort')) {
			$query->order(['Attachments.created' => 'DESC']);
		} else {
			if (isset($this->request->query['asset_id']) ||
				isset($this->request->query['all'])
			) {
				$finder = 'versions';
				$model = $this->request->query('model');
				$foreignKey = $this->request->query('foreign_key');
				$this->set(compact('model', 'foreignKey'));
				unset($this->request->query['model']);
				unset($this->request->query['foreign_key']);

				if (!$this->request->query('sort')) {
					$query->order([
						$this->Attachments->aliasField('id') => 'desc',
					]);
				}
			}
		}

		if ($isChooser) {
			if ($this->request->query['chooser_type'] == 'image') {
				$query->where([
					'Assets.mime_type LIKE' => 'image/%',
				]);
			} else {
				$query->where([
					'Assets.mime_type NOT LIKE' => 'image/%',
				]);
			}
		}

		$query->find('search', [
			'search' => $this->request->query
		]);

		if (isset($finder)) {
			$query->find($finder);
		}

		$this->set('attachments', $this->paginate($query));

		if ($this->request->query('links') || $this->request->query('chooser')) {
			$this->viewBuilder()->setLayout('admin_popup');
			$this->render('admin_chooser');
		}
	}

/**
 * Admin add
 *
 * @return void
 * @access public
 */
	public function add() {
		$this->set('title_for_layout', __d('croogo', 'Add Attachment'));

		if ($this->request->query('editor')) {
			$this->viewBuilder()->setLayout('admin_popup');
		}

		if ($this->request->is('post')) {

/*
			if (empty($this->data['Attachments'])) {
				$this->Attachments->invalidate('file', __d('croogo', 'Upload failed. Please ensure size does not exceed the server limit.'));
				return;
			}
*/

			$entity = $this->Attachments->newEntity($this->request->data());
			$attachment = $this->Attachments->save($entity);

			if ($attachment) {
				/*
				$attachmentId = $saved->id;
				$attachment = $this->Attachments->find()
					->where([
						$this->Attachments->aliasField('id') => $attachmentId,
					])
					->contain([
						'Assets',
						'Assets.AssetUsages',
					])
					->first();
				*/
				$eventKey = 'Controller.AssetsAttachment.newAttachment';
				Croogo::dispatchEvent($eventKey, $this, compact('attachment'));
			} else {
				Log::error('Failed saving attachments');
			}

			if ($this->request->is('ajax')) {
				$files = array();
				$error = false;

				if (empty($attachment->errors())) {
					$this->viewBuilder()->className('Json');
					$files = array(array(
						'url' => $attachment->path,
						'thumbnail_url' => $attachment->path,
						'name' => $attachment->title,
						'type' => $attachment->mime_type,
						'size' => $attachment->filesize,
					));
				} else {
					$errors = $this->Attachments->errors();
					$files = array(array('error' => $errors));
					$error = implode("\n", $errors);
				}

				$this->set(compact('files', 'error'));
				$this->set('_serialize', array('files', 'error'));
				return;
			} else {
				// noop
			}

			if ($attachment) {
				$this->Flash->success(__d('croogo', 'The Attachment has been saved'));
				$url = array();
				if (isset($saved->asset->asset_usage[0])) {
					$usage = $saved->asset->asset_usage[0];
					if (!empty($usage->model) && !empty($usage->foreign_key)) {
						$url['?']['model'] = $usage->model;
						$url['?']['foreign_key'] = $usage->foreign_key;
					}
				}
				if ($this->request->query('editor')) {
					$url = array_merge($url, array('action' => 'browse'));
				} else {
					$url = array_merge($url, array('action' => 'index'));
				}
				return $this->redirect($url);
			} else {
				$this->Flash->error(__d('croogo', 'The Attachment could not be saved. Please, try again.'));
			}
		} else {
			// noop
		}

		$attachment = $this->Attachments->newEntity();
		$this->set(compact('attachment'));
	}

/**
 * Admin edit
 *
 * @param int $id
 * @return void
 * @access public
 */
	public function edit($id = null) {
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
			$this->Flash->error(__d('croogo', 'Invalid Attachment'));
			return $this->redirect($redirect);
		}
		$attachment = $this->Attachments->get($id, [
			'contain' => [
				'Assets',
			],
		]);
		if ($this->request->is('post')) {
			$attachment = $this->Attachments->patchEntity($this->request->data());
			if ($this->Attachments->save($attachment)) {
				$this->Flash->success(__d('croogo', 'The Attachment has been saved'));
				return $this->redirect($redirect);
			} else {
				$this->Flash->error(__d('croogo', 'The Attachment could not be saved. Please, try again.'));
			}
		}
		$this->set(compact('attachment'));
	}

/**
 * Admin delete
 *
 * @param int $id
 * @return void
 * @access public
 */
	public function delete($id = null) {
		if (!$id) {
			$this->Flash->error(__d('croogo', 'Invalid id for Attachment'));
			return $this->redirect(array('action' => 'index'));
		}

		$redirect = array('action' => 'index');
		if (!empty($this->request->query)) {
			$redirect = array_merge(
				$redirect,
				array('action' => 'browse', '?' => $this->request->query)
			);
		}

		$attachment = $this->Attachments->get($id);
		$this->Attachments->connection()->begin();
		if ($this->Attachments->delete($attachment)) {
			$this->Attachments->connection()->commit();
			$this->Flash->success(__d('croogo', 'Attachment deleted'));
			return $this->redirect($redirect);
		} else {
			$this->Flash->error(__d('croogo', 'Invalid id for Attachment'));
			return $this->redirect($redirect);
		}
	}

/**
 * Admin browse
 *
 * @return void
 * @access public
 */
	public function browse() {
		$this->viewBuilder()->setLayout('admin_popup');
		$this->index();
	}

	public function listing() {
		if ($this->request->is('ajax')) {
			$this->viewBuilder()->setLayout('ajax');
			$this->paginate['limit'] = 100;
		}

		$query = $this->Attachments
			->find('search', [
				'search' => $this->request->query,
			])
			->find('modelAttachments');
		$attachments = $this->paginate($query);
		$this->set(compact('attachments'));
	}

	public function resize($id = null) {
		if (empty($id)) {
			throw new NotFoundException('Missing Asset Id to resize');
		}

		$result = false;
		if (!empty($this->request->data)) {
			$width = $this->request->data['width'];
			try {
				$result = $this->Attachments->createResized($id, $width, null);
			} catch (Exception $e) {
				$result = $e->getMessage();
			}
		}

		$this->set(compact('result'));
		$this->set('_serialize', 'result');
	}

}