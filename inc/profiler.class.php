<?php

class PluginDevProfiler extends CommonGLPI {

   /** @var DateTime */
   private static $session_start;

   /** @var string */
   private static $session_uuid;

   /** @var PluginDevProfilerSection[] */
   private static $prev_sections = [];

   /** @var PluginDevProfilerSection[] */
   private static $current_sections = [];

   public static function start(string $name, string $category = 'core'): void {
      if (array_key_exists($name, self::$current_sections)) {
         \Toolbox::logWarning("Profiler section $name already started");
         return;
      }
      if (self::$session_start === null) {
         self::$session_start = new \DateTime('now');
         self::$session_uuid = \Ramsey\Uuid\Uuid::uuid4();//uniqid('', true);
      }
      self::$current_sections[$name] = new PluginDevProfilerSection($category, microtime(true) * 1000);
      self::logProfilerSession("[$category]\tStarted section $name");
   }

   public static function end(string $name): void {
      if (!array_key_exists($name, self::$current_sections)) {
         \Toolbox::logWarning("Profiler section $name has not been started");
         return;
      }
      self::$current_sections[$name]->end(microtime(true) * 1000);
      self::$prev_sections[$name] = self::$current_sections[$name];
      unset(self::$current_sections[$name]);
      $category = self::$prev_sections[$name]->getCategory();
      $duration = self::$prev_sections[$name]->getDuration();
      self::logProfilerSession("[$category]\tEnded section $name after $duration ms");
   }

   private static function getProfilerLogPath(): string {
      //return GLPI_ROOT.'/'.\Plugin::getWebDir('dev', false).'/var/profiler_sessions/'.self::$session_start->format('Y-m-d H-i-s-u').'.log';
      return GLPI_ROOT.'/'.\Plugin::getWebDir('dev', false).'/var/profiler_sessions/'.self::$session_start->format('Y-m-d').'.log';
   }

   private static function logProfilerSession($message): void {
      $log_path = self::getProfilerLogPath();
      $ts = (new \DateTime('now'))->format('Y-m-d H:i:s:u');
      $session_uuid = self::$session_uuid;
      $log = fopen($log_path, 'ab');
      fwrite($log, "[$ts]\t[$session_uuid]".$message . "\n");
      fclose($log);
   }
}
