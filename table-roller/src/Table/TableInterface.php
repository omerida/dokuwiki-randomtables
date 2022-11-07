<?php

namespace TableRoller\Table;

interface TableInterface {
   public function roll(int $times = 1) : array;
   public function rollOnce() : string;
   public function rollWithoutRepeats(int $times = 1) : array;
   public function __invoke() : string;
   public function __toString() : string;
}