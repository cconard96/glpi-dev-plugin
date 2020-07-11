<?php

class PluginDevThemedesigner extends CommonGLPI {

   public static function getTypeName($nb = 0)
   {
      return _x('tool', 'Theme designer (Experimental)', 'dev');
   }
}