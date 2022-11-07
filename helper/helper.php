<?php
/**
 * DokuWiki Plugin randomtable (Helper Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Oscar Merida <oscar@oscarm.org>
 */
class helper_plugin_randomtables_helper extends \dokuwiki\Extension\Plugin
{
    public function getDB(): ?helper_plugin_sqlite
    {
		/** @var helper_plugin_sqlite $sqlite */
		$sqlite = plugin_load('helper', 'sqlite');

		if(!$sqlite){
			msg('This plugin requires the sqlite plugin. Please install it', -1);
			return null;
		}
		// initialize the database connection
		if(!$sqlite->init('randomtables', DOKU_PLUGIN . 'randomtables/db/')){
			return null;
		}
		
		return $sqlite;
    }
}

