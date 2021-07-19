<?php

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class PluginDevToolbox {

   public static function getTwig(): Environment
   {
      static $twig = null;

      if ($twig === null) {
         $loader = new FilesystemLoader(Plugin::getPhpDir('dev') . '/templates');
         $options = array(
            'strict_variables' => false,
            'debug' => false,
            'cache' => false
         );
         $twig = new Environment($loader, $options);
      }
      return $twig;
   }
}
