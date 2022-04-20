<?php

class PluginDevMenu extends CommonGLPI {

   /**
    * Get name of this type by language of the user connected
    *
    * @param integer $nb number of elements
    * @return string name of this type
    */
   public static function getTypeName($nb = 0) {
      return _x('plugin_info', 'GLPI Development Helper', 'dev');
   }

   public static function getMenuName()
   {
      return _x('plugin_info', 'GLPI Development Helper', 'dev');
   }

   public static function getIcon() {
      return 'fas fa-code';
   }

   /**
    * Check if can view item
    *
    * @return boolean
    */
   static function canView() {
      return ((int) $_SESSION['glpi_use_mode']) === Session::DEBUG_MODE;
   }
}
