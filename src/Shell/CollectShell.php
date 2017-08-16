<?php

namespace Xintesa\Assets\Shell;

use Cake\Console\Shell;
use Cake\ORM\TableRegistry;

class CollectShell extends Shell {

	public function getOptionParser() {
		return parent::getOptionParser()
			->description(__d('assets', 'Scan directory and import record to database'))
			->addArguments(array(
				'dir' => array(
					'help' => __d('assets', 'Path to scan'),
					'required' => true,
				),
			))
			->addOptions(array(
				'regex' => array(
					'help' => __d('assets', 'File name Regex'),
					'required' => false,
					'short' => 'r',
				),
			));
	}

	public function main() {
		$dir = $this->args[0];
		$regex = '.*\.(jpg)|(jpeg)|(png)|(pdf)|(mp4)';
		if (strpos($dir, ',') !== false) {
			$dir = explode(',', $dir);
		}
		if (isset($this->params['regex'])) {
			$regex = $this->params['regex'];
		}
		$Attachment = TableRegistry::get('Xintesa/Assets.Attachments');
		$importTask = $Attachment->importTask((array)$dir, $regex);
		if (!empty($importTask['error'])) {
			$this->out('<error>Warnings/Errors:</error>');
			$tasks = $errors = 0;
			foreach ($importTask['error'] as $message) {
				$tasks++;
				if ($message) {
					$this->err("\t$message");
					$errors++;
				}
			}
			$this->out();
			if ($tasks - $errors > 0) {
				$this->out('<warning>' . __d('assets', 'Task has {0} tasks and {1} errors?', $tasks, $errors) . '</warning>');
				$continue = $this->in('Continue?', array('Y', 'n'), 'n');
				if ($continue == 'n') {
					$this->out('Aborted');
					return $this->_stop();
				}
			}
		}
		$result = $Attachment->runTask($importTask);

		$message = __d('assets', 'Processed {0} files with {1} errors', $result['imports'], $result['errors']);
		if ($result['errors'] == 0) {
			$message = sprintf('<warning>%s</warning>', $message);
			$this->out($message);
		} else {
			$message = sprintf('<error>%s</error>', $message);
			$this->err($message);
		}
	}

}
