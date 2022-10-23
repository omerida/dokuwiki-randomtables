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

		$x = $db->storeEntry('rtables', [
			'id' => $id,
			'rows' => json_encode($json)
		]);
	}
}