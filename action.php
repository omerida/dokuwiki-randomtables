<?php
/**
 * DokuWiki Plugin randomtable (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Oscar Merida <oscar@oscarm.org>
 */
class action_plugin_randomtables extends \dokuwiki\Extension\ActionPlugin
{
	public function register(Doku_Event_Handler $controller)
	{
		$controller->register_hook('randomtables_save', 'AFTER', $this, 'save');
		$controller->register_hook('AJAX_CALL_UNKNOWN', 'BEFORE', $this,'ajax_call');
	}

	public function save(Doku_Event $event, $param)
	{
		try {
            $helper = plugin_load('helper', 'randomtables');
            $db = $helper->getDB();
        } catch (Exception $e) {
            msg($e->getMessage(), -1);
            return false;
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

	function ajax_call(Doku_Event $event, $param) {

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
            $helper = plugin_load('helper', 'randomtables');
            $db = $helper->getDB();
        } catch (Exception $e) {
            msg($e->getMessage(), -1);
            return false;
        }

		$result = $db->query('SELECT * FROM rtables WHERE id=? LIMIT 1', $tableId); 
		$table = $db->res_fetch_assoc($result);
		$rows = json_decode($table['rows']);

		// fake rolling for now
		shuffle($rows);
		$pick = array_pop($rows);

		header('Content-Type: application/json');
		echo json_encode(['result' => $pick->result]);
	}
}