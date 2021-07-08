<?php

class PluginDevProfilerSection {

   /** @var string */
   private $category;

   /** @var int */
   private $start;

   /** @var int */
   private $end;

   public function __construct(string $category, int $start) {
      $this->category = $category;
      $this->start = $start;
   }

   public function end(int $time): void {
      $this->end = $time;
   }

   public function getStart(): int {
      return $this->start;
   }

   public function getEnd(): int {
      return $this->end;
   }

   public function getCategory(): string {
      return $this->category;
   }

   public function getDuration(): int {
      $end = $this->end ?? (int) (microtime(true) * 1000);
      return $end- $this->start;
   }

   public function isFinished(): bool {
      return $this->end !== null;
   }
}
