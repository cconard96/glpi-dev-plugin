<?php

require_once('../vendor/autoload.php');

class PluginDevCssdesigner extends CommonGLPI {

   public static function showCSSDesignerForm($plugin, $stylesheet)
   {
      if (!Session::haveRight('config', UPDATE)) {
         return false;
      }

      // Codemirror lib
      echo Html::css('public/lib/codemirror.css');
      echo Html::script("public/lib/codemirror.js");

      echo "<div class='spaced'>";
      echo "<form method='post' name=form action='".self::getSearchURL()."'>";
      echo "<div id='plugin_css_container' class='plugin_css_container'>";
      // wrap call in function to prevent modifying variables from current scope
      call_user_func(static function() use($plugin, $stylesheet) {
         $_POST  = [
            'plugin'       => $plugin,
            'stylesheet'   => $stylesheet
         ];
         include Plugin::getPhpDir('dev') . '/ajax/pluginCssCode.php';
      });
      echo "</div>";

      echo "<div class='center'>";
      echo "<input type='hidden' name='plugin' value='".$plugin."'>";
      echo "<input type='hidden' name='stylesheet' value='".$stylesheet."'>";
      echo "<input type='submit' name='update' value=\""._sx('button', 'Save')."\" class='submit'>";
      echo "</div>";
      Html::closeForm();

      echo "</div>";
   }
}