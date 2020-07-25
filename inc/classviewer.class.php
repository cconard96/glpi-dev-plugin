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
      $options = array_filter($options, static function($k) {
         return is_numeric($k);
      }, ARRAY_FILTER_USE_KEY);
      return $options;
   }

   public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
   {
      return self::getTypeName();
   }

   public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
   {
      self::showForItem($item);
   }

   public static function showForItem(CommonGLPI $item)
   {
      echo "<div id='classview-container' data-embedded='true'>";
      echo "<div class='sidebar' style='display: none'></div>";
      echo "<div class='info-container'>";

      echo "</div>";
      echo "</div>";
      $item_class = get_class($item);
      echo Html::scriptBlock("window.glpiDevHelper.showClassInfo('{$item_class}')");
   }

   public function showForm()
   {
      $tables = self::getTables();

      $loadedClasses = get_declared_classes();
      $glpiClasses = [];

      foreach ($loadedClasses as $class) {
         if (is_subclass_of($class, 'CommonGLPI')) {
            $glpiClasses[] = $class;
         }
      }
      sort($glpiClasses);


      echo "<div id='classview-container'>";
      echo "<div class='sidebar'>";
      echo "<input name='search'/>";
      echo "<ul>";
      foreach ($glpiClasses as $class) {
         echo "<li><a href='#".$class."'>$class</a></li>";
      }
      echo "</ul>";
      echo "</div>";

      echo "<div class='info-container'>";

      echo "</div>";
      echo "</div>";
   }
}