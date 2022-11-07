<?php

namespace TableRoller\Table;

class BaseTable implements TableInterface
{
   protected array $options = [];
   protected int $count;
   protected int $upper_bound;
   protected array $keys;

   protected function pickOne() : int {
      return $this->keys[rand(0, $this->count - 1)];
   }

   public function roll(int $times = 1) : array {
      $result = [];

      for ($i = 0; $i < $times; $i++) {
         $result[] = $this->proceessResult($this->options[$this->pickOne()]);
      }

      return $result;
   }

   protected function setOptions(array $options) {

      foreach ($options as $entry) {
         if (preg_match('/^(\d+)\:(.*)$/', $entry, $match)) {
            $this->options[(int)$match[1]] = trim($match[2]);
         } elseif (preg_match('/^(\d+)\-(\d+)\:(.*)$/', $entry, $match)) {
            foreach(range($match[1], $match[2]) as $key) {
               $this->options[$key] = $match[3];
            }
         } else {
            $this->options[] = $entry;
         }
      }
      
	  $this->count = count($this->options);
      $this->keys = array_keys($this->options);
   }

   public function rollOnce() : string {
      return $this->proceessResult($this->options[$this->pickOne()]);
   }

   public function rollWithoutRepeats(int $times = 1) : array {
      $result = [];
      $used = [];

      while (count($result) < $times) {
         $pick = $this->pickOne();

         if (!in_array($pick, $used)) {
            $result[] = $this->proceessResult($this->options[$pick]);
            $used[] = $pick;
         }
      }

      return $result;
   }

   private function proceessResult(string $result) {

      if (false !== strpos($result, '[')) {
         $result = preg_replace_callback('/\[([^]]+)\]/', function($match) {
            $options = explode('|', $match[1]);
            shuffle($options);
            return $options[0];
         }, $result);

      }

      if (false !== strpos($result, '{')) {
         $result = preg_replace_callback('/{(\d*)[Dd](\d+)(\+|\-)?(\d+)?}/', function($match) {
			return $this->rollDie($match[0]);
         }, $result);

      }

      return $result;
   }

   public function rollDie(string $formula) {
      $formula = strtoupper(trim($formula));
      preg_match('/(\d*)[Dd](\d+)(\+|\-)?(\d+)?/', $formula, $parts);
      $rolls = empty($parts[1]) ? 1 : $parts[1];
      $die = (int) $parts[2];

      $mod = 0;
      if (!empty($parts[3])) {
         $mod = (int)($parts[3] . $parts[4]);
      }
      $result = 0;

      for ($i = 0; $i < $rolls; $i++) {
         $result += rand(1, $die);
      }

      $result += $mod;
      return $result;
   }

   public function __invoke() : string {
      return $this->rollOnce();
   }

   public function __toString() : string {
      return $this->rollOnce();
   }
}