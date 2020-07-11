<?php

require_once('../vendor/autoload.php');
use Nette\PhpGenerator\GlobalFunction;
use Nette\PhpGenerator\PhpFile;

class PluginDevPlugincreator extends CommonGLPI {

   public static function getTypeName($nb = 0)
   {
      return _x('tool', 'Plugin creator', 'dev');
   }

   public function showCreateForm()
   {
      if (!Session::haveRight('config', UPDATE)) {
         return false;
      }

      echo "<form name='form' action=\"".static::getFormURL(true)."\" method='post'>";
      echo "<input name='init' type='hidden' value='true'/>";
      echo "<div class='center' id='tabsbody'>";
      echo "<table class='tab_cadre_fixe'><thead>";
      echo "<th colspan='4'>" . _x('form_section', 'Plugin Info', 'dev') . '</th></thead>';
      echo '<td>' . _x('form_field', 'Name', 'dev') . '</td>';
      echo '<td>';
      echo Html::input('name', ['title' => _x('tooltip', 'The friendly name of the plugin', 'dev')]);
      echo '</td><td>' ._x('form_field', 'Plugin version', 'dev'). '</td><td>';
      echo Html::input('version', [
         'value' => '1.0.0',
         'title' => _x('tooltip', 'The starting version of the plugin', 'dev')
      ]);
      echo '</td></tr><tr><td>' ._x('form_field', 'Minimum GLPI version (inclusive)', 'dev'). '</td><td>';
      echo Html::input('min_glpi');
      echo '</td>';
      echo '<td>' ._x('form_field', 'Maximum GLPI version (exclusive)', 'dev'). '</td><td>';
      echo Html::input('max_glpi');
      echo '</td></tr>';
      echo '</table>';

      echo "<table class='tab_cadre_fixe'>";
      echo "<tr class='tab_bg_2'>";
      echo "<td colspan='4' class='center'>";
      echo "<input type='submit' name='update' class='submit' value=\""._sx('action', 'Initialize', 'dev'). '">';
      echo '</td></tr>';
      echo '</table>';
      echo '</div>';
      Html::closeForm();
   }

   public static function initPlugin(array $options) {
      global $CFG_GLPI;

      $p = [
         'identifier'   => null
      ];
      $p = array_replace($p, $options);
      if (empty($p['name'])) {
         return false;
      }
      if ($p['identifier'] === null) {
         $p['identifier'] = str_replace(' ', '', strtolower($p['name']));
      }

      // Set the plugins path
      $plugins_dir = '../../../plugins';
      $plugin_dir = $plugins_dir . '/' . $p['identifier'];

      // Check plugin dir does not exist
      if (is_dir($plugin_dir)) {
         return false;
      }

      // Create plugins dir(s)
      if (!mkdir($plugin_dir) && !is_dir($plugin_dir)) {
         throw new \RuntimeException(sprintf('Directory "%s" was not created', $plugin_dir));
      }

      // Create hook.php
      $install_func = new GlobalFunction("plugin_{$p['identifier']}_install");
      $install_func->setBody('return true;');
      $uninstall_func = new GlobalFunction("plugin_{$p['identifier']}_uninstall");
      $uninstall_func->setBody('return true;');
      $hook_file = fopen($plugin_dir . '/hook.php', 'wb+');
      fwrite($hook_file, '<?php' . PHP_EOL);
      fwrite($hook_file, $install_func . PHP_EOL);
      fwrite($hook_file, $uninstall_func . PHP_EOL);
      fclose($hook_file);
      chmod($plugin_dir . '/hook.php', 0660);

      // Create setup.php
      $init_func = new GlobalFunction("plugin_init_{$p['identifier']}");
      $init_func->setBody(<<<EOF
global \$PLUGIN_HOOKS;
\$PLUGIN_HOOKS['csrf_compliant']['{$p['identifier']}'] = true;
EOF
      );
      $uc_identifier = strtoupper($p['identifier']);
      $version_func = new GlobalFunction("plugin_version_{$p['identifier']}");
      $version_func->setBody(<<<EOF
return [
      'name'         => __('{$p['name']}', '{$p['identifier']}'),
      'version'      => PLUGIN_{$uc_identifier}_VERSION,
      'author'       => '{$p['author']}',
      'license'      => '{$p['license']}',
      'homepage'     =>'{$p['homepage']}',
      'requirements' => [
         'glpi'   => [
            'min' => PLUGIN_{$uc_identifier}_MIN_GLPI,
            'max' => PLUGIN_{$uc_identifier}_MAX_GLPI
         ]
      ]
   ];
EOF
      );
      $prerequisites_func = new GlobalFunction("plugin_{$p['identifier']}_check_prerequisites");
      $prerequisites_func->setBody(<<<EOF
if (!method_exists('Plugin', 'checkGlpiVersion')) {
      \$version = preg_replace('/^((\d+\.?)+).*$/', '$1', GLPI_VERSION);
      \$matchMinGlpiReq = version_compare(\$version, PLUGIN_{$uc_identifier}_MIN_GLPI, '>=');
      \$matchMaxGlpiReq = version_compare(\$version, PLUGIN_{$uc_identifier}_MAX_GLPI, '<');
      if (!\$matchMinGlpiReq || !\$matchMaxGlpiReq) {
         echo vsprintf(
            'This plugin requires GLPI >= %1\$s and < %2\$s.',
            [
               PLUGIN_{$uc_identifier}_MIN_GLPI,
               PLUGIN_{$uc_identifier}_MAX_GLPI,
            ]
         );
         return false;
      }
   }
   return true;
EOF
      );
      $checkconfig_func = new GlobalFunction("plugin_{$p['identifier']}_check_config");
      $checkconfig_func->setBody('return true;');
      $setup_file = fopen($plugin_dir . '/setup.php', 'wb+');
      fwrite($setup_file, '<?php' . PHP_EOL . PHP_EOL);
      fwrite($setup_file, "define('PLUGIN_{$uc_identifier}_VERSION', '{$p['version']}');" . PHP_EOL);
      fwrite($setup_file, "define('PLUGIN_{$uc_identifier}_MIN_GLPI', '{$p['min_glpi']}');" . PHP_EOL);
      fwrite($setup_file, "define('PLUGIN_{$uc_identifier}_MAX_GLPI', '{$p['max_glpi']}');" . PHP_EOL . PHP_EOL);
      fwrite($setup_file, $init_func . PHP_EOL);
      fwrite($setup_file, $version_func . PHP_EOL);
      fwrite($setup_file, $prerequisites_func . PHP_EOL);
      fwrite($setup_file, $checkconfig_func . PHP_EOL);
      fclose($setup_file);
      chmod($plugin_dir . '/setup.php', 0660);

      return true;
   }
}