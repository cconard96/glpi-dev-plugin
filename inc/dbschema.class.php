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
      return $schema;
   }
}