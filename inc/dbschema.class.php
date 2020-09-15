<?php

class PluginDevDbschema extends CommonGLPI {

   public static function getTypeName($nb = 0)
   {
      return _x('tool', 'DB Schema', 'dev');
   }

   public static function getTables(): array
   {
      global $DB;

      $tables = [];
      $result = $DB->listTables();
      foreach ($result as $r) {
         $tables[] = $r['TABLE_NAME'];
      }

      return $tables;
   }

   public static function getTableSchema(string $table): array
   {
      global $DB;

      $schema = $DB->listFields($table);
      // List Indexes
      /** @var mysqli_result $result */
      $result = $DB->query("SHOW INDEX FROM $table FROM ".$DB->dbdefault);
      $indexes = $result->fetch_all(MYSQLI_ASSOC);

      // Cleanup Indexes Data
      foreach ($indexes as &$index) {
         $index['Unique'] = !$index['Non_unique'];
      }

      return [
         'fields'    => $schema,
         'indexes'   => $indexes
      ];
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
      echo "<div id='dbschemaview-container' data-embedded='true'>";
      echo "<div class='sidebar' style='display: none'></div>";
      echo "<div class='info-container'>";

      echo "</div>";
      echo "</div>";
      echo Html::scriptBlock("window.glpiDevHelper.showDBTableSchema('{$item::getTable()}')");
   }

   public function showForm()
   {
      $tables = self::getTables();

      sort($tables);

      echo "<div id='dbschemaview-container'>";
      echo "<div class='sidebar'>";
      echo "<input name='search'/>";
      echo "<ul>";
      foreach ($tables as $table) {
         echo "<li><a href=''>$table</a></li>";
      }
      echo "</ul>";
      echo "</div>";

      echo "<div class='info-container'>";

      echo "</div>";
      echo "</div>";
   }
}