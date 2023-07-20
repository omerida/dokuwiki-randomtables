<?php

namespace TableRoller\Table;

class Manager
{
	static private $embedCalls = 0;

	public function __construct(
		private \helper_plugin_sqlite $db
	){}

	private static array $tables;

	public function rollOn(string $name): string
	{
		$table = $this->getTable($name);
		$result = $table->rollOnce();
		return preg_replace_callback('/{{roll on:\s*([A-Za-z0-9_]+)\s*}}/i', [$this, 'embedResult'], $result);
	}

	private function embedResult($matches): string
	{
		self::$embedCalls++;
		if (self::$embedCalls < 20) {
			$table = $this->getTable($matches[1]);
			return trim($table->rollOnce());
		}

		return "Too many recursive calls";
	}

	private function getTable(string $name): TableInterface
	{
		if (!isset(self::$tables[$name])) {
			$result = $this->db->query('SELECT * FROM rtables WHERE id=? LIMIT 1', $name);
			$row = $this->db->res_fetch_assoc($result);
			if ($row['rows']) {
				self::$tables[$name] = new DokuwikiJsonTable($row['rows']);
			} else {
				self::$tables[$name] = new BaseTable();
			}
		}

		return self::$tables[$name];
	}
}
