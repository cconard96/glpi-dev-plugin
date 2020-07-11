<?php

define('PLUGIN_DEV_VERSION', '1.0.0');
define('PLUGIN_DEV_MIN_GLPI', '9.5.0');
define('PLUGIN_DEV_MAX_GLPI', '9.6.0');

function plugin_init_dev() {
   global $PLUGIN_HOOKS;
   $PLUGIN_HOOKS['csrf_compliant']['dev'] = true;
   $PLUGIN_HOOKS['add_css']['dev'][] = 'css/dev.css';
   $PLUGIN_HOOKS['add_javascript']['dev'][] = 'js/dev.js';

   if (Session::haveRight('config', UPDATE) && $_SESSION['glpi_use_mode'] == Session::DEBUG_MODE) {
      $PLUGIN_HOOKS['menu_toadd']['dev'] = ['plugins' => 'PluginDevMenu'];
   }
}

function plugin_version_dev() {

   return [
      'name' => __("GLPI Development Helper", 'dev'),
      'version' => PLUGIN_DEV_VERSION,
      'author'  => 'Curtis Conard',
      'license' => 'GPLv2',
      'homepage'=>'https://github.com/cconard96/',
      'requirements'   => [
         'glpi'   => [
            'min' => PLUGIN_DEV_MIN_GLPI,
            'max' => PLUGIN_DEV_MAX_GLPI
         ]
      ]
   ];
}

function plugin_dev_check_prerequisites() {
   if (!method_exists('Plugin', 'checkGlpiVersion')) {
      $version = preg_replace('/^((\d+\.?)+).*$/', '$1', GLPI_VERSION);
      $matchMinGlpiReq = version_compare($version, PLUGIN_DEV_MIN_GLPI, '>=');
      $matchMaxGlpiReq = version_compare($version, PLUGIN_DEV_MAX_GLPI, '<');
      if (!$matchMinGlpiReq || !$matchMaxGlpiReq) {
         echo vsprintf(
            'This plugin requires GLPI >= %1$s and < %2$s.',
            [
               PLUGIN_DEV_MIN_GLPI,
               PLUGIN_DEV_MAX_GLPI,
            ]
         );
         return false;
      }
   }
   return true;
}

function plugin_dev_check_config()
{
   return true;
}