<?php

class PluginDevClassviewer extends CommonGLPI {

   public static function getTypeName($nb = 0)
   {
      return _x('tool', 'Class viewer', 'dev');
   }

   public static function getSearchOptions($class) {
      /** @var CommonGLPI $item */
      $item = new $class();

      try {
         $options = Search::getOptions($item::getType());
      } catch (Exception $e) {
         $options = [];
      }
      return $options;
   }
}