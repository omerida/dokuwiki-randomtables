<?php

namespace TableRoller\Table;

class DokuwikiJsonTable extends BaseTable
{
   public function __construct(string $json)
   {
		$rows = json_decode($json);
		// transform our json into what the table roller class expects
		// this is a bit of a round-trip but it helps make the options
		// what the class exects
		$options = array_map(function($item) {
			return sprintf('%d-%d: %s', $item->min, $item->max, $item->result);
		}, $rows);

        $this->setOptions($options);
   }
}