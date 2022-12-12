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

      if (Plugin::isPluginActive('datainjection')) {
          // Add injectable property to all $options set to 0
          foreach ($options as &$option) {
              $option['injectable'] = 0;
          }
          unset($option);

          // Get injection class
          $injection_class = 'PluginDatainjection' . $class . 'Injection';
          if (class_exists($injection_class)) {
              /** @var PluginDatainjectionInjectionInterface $injection */
              $injection = new $injection_class();
              $injection_options = $injection->getOptions($class);
              foreach ($injection_options as $id => $injection_option) {
                  if (isset($options[$id])) {
                      $options[$id]['injectable'] = $injection_option['injectable'] ?? 0;
                  }
              }
          }
      }

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
      echo Html::scriptBlock("GlpiDevClassViewer.showClassInfo('{$item_class}')");
   }

   public function showForm()
   {
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
         echo "<li><a href=''>$class</a></li>";
      }
      echo "</ul>";
      echo "</div>";

      echo "<div class='info-container'>";

      echo "</div>";
      echo "</div>";
   }

   public static function getUnlinkedSearchOptions($class)
   {
      global $DB;

      $unlinked_searchopts = [];
      try {
         $table = $class::getTable();
         $found_options = array_filter(self::getSearchOptions($class), static function ($opt) use ($table) {
            return strcmp($opt['table'], $table) === 0 && (!isset($opt['datatype']) || $opt['datatype'] !== 'specific');
         });
         $found_fields = $DB->listFields($class::getTable());
         if ($found_fields) {
            $local_fields = array_column(array_filter($found_fields, static function ($field_def) {
               return !isForeignKeyField($field_def['Field']);
            }), 'Field');
            $unlinked_searchopts = array_diff(array_column($found_options, 'field'), $local_fields);
         }

         return $unlinked_searchopts;
      } catch (Exception $e) {
         return [];
      }
   }

   public static function getMissingSearchOptions($class)
   {
      global $DB;

      $missing_searchopts = [];
      try {
         $table = $class::getTable();
         $all_options = self::getSearchOptions($class);
         $found_options = array_filter($all_options, static function($opt) use ($table) {
            return strcmp($opt['table'], $table) === 0;
         });
         $found_fields = $DB->listFields($class::getTable());
         if ($found_fields) {
            $local_fields = array_column(array_filter($found_fields, static function ($field_def) {
               return !isForeignKeyField($field_def['Field']);
            }), 'Field');
            $missing_searchopts = array_diff($local_fields, array_column($found_options, 'field'));
         }

         // Ignore is_deleted field. It is depicted differently than other fields.
         $missing_searchopts = array_filter($missing_searchopts, static function($opt) {
            return !in_array($opt, ['is_deleted']);
         });
         return $missing_searchopts;
      } catch (Exception $e) {
         return [];
      }
   }
}
