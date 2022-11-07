<?php

/**
 * DokuWiki Plugin randomtable (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Oscar Merida <oscar@oscarm.org>
 */

class action_plugin_randomtables_ajax extends \dokuwiki\Extension\ActionPlugin
{
	public function register(Doku_Event_Handler $controller)
	{
		$controller->register_hook('randomtables_save', 'AFTER', $this, 'save');
		$controller->register_hook('AJAX_CALL_UNKNOWN', 'BEFORE', $this,'ajax_call');
	}

	public function save(Doku_Event $event, $param): void
	{
		try {
			$helper = $this->loadHelper('randomtables_helper');
            $db = $helper->getDB();
        } catch (Exception $e) {
            msg($e->getMessage(), -1);
            return;
        }

		$data = $event->data;
		$id = $data['id'];
		if (!$id) {
			return;
		}

		$json = [];
		foreach ($data['lines'] as $l) {
			$json[] = ['min' => $l[0], 'max' => $l[1], 'result' => $l[2]];
		}

		$db->storeEntry('rtables', [
			'id' => $id,
			'rows' => json_encode($json)
		]);
	}

	function ajax_call(Doku_Event $event, $param):void
    {
		if ($event->data !== 'plugin_randomtable_roll') {
			return;
		}

		//no other ajax call handlers needed
		$event->stopPropagation();
		$event->preventDefault();


		$tableId = isset($_POST['table_id']) ? trim($_POST['table_id']) : null;
		if (!$tableId || !preg_match('/^[A-Za-z0-9_]+$/', $tableId)) {
			return;
		}

		// get the DB
		try {
			$helper = $this->loadHelper('randomtables_helper');
            $db = $helper->getDB();
        } catch (Exception $e) {
            msg($e->getMessage(), -1);
            return;
        }

		$this->setupAutoloader();


		$manager = new TableRoller\Table\Manager($db);
		$pick = $manager->rollOn($tableId);

		header('Content-Type: application/json');
		echo json_encode(['result' => trim($pick)]);
	}

	private function setupAutoloader()
	{
		spl_autoload_register(function ($class) {

			// project-specific namespace prefix
			$prefix = 'TableRoller\\';

			// base directory for the namespace prefix
			$base_dir = __DIR__ . '/../table-roller/src/';

			// does the class use the namespace prefix?
			$len = strlen($prefix);
			if (strncmp($prefix, $class, $len) !== 0) {
				// no, move to the next registered autoloader
				return;
			}

			// get the relative class name
			$relative_class = substr($class, $len);

			// replace the namespace prefix with the base directory, replace namespace
			// separators with directory separators in the relative class name, append
			// with .php
			$file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

			// if the file exists, require it
			if (file_exists($file)) {
				require $file;
			}
        });
	}
}