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

      echo '<tr><td>' ._x('form_field', 'Authors', 'dev'). '</td><td>';
      echo Html::input('authors');
      echo '&nbsp;';
      Html::showToolTip(_x('form_field_tt', 'Comma separated', 'dev'));
      echo '</td>';
      echo '<td>' ._x('form_field', 'License', 'dev'). '</td><td>';
      echo Html::input('license');
      echo '</td></tr>';

      echo '<tr><td>' ._x('form_field', 'Homepage', 'dev'). '</td><td>';
      echo Html::input('homepage');
      echo '</td>';
      echo '<td>' ._x('form_field', 'Language', 'dev'). '</td><td>';
      Dropdown::showLanguages('language', [
         'value'  => $_SESSION['glpilanguage']
      ]);
      echo '</td>';
      echo '</tr>';

      echo '<tr><td>' ._x('form_field', 'Description (Short)', 'dev'). '</td><td>';
      Html::textarea([
         'name'   => 'description_short',
         'cols'   => 45,
         'rows'   => 4
      ]);
      echo '</td><td></td></tr>';

      echo '<tr><td>' ._x('form_field', 'Description (Long)', 'dev'). '</td><td>';
      Html::textarea([
         'name' => 'description_long',
         'cols'   => 45,
         'rows'   => 4
      ]);
      echo '</td><td></td></tr>';
      echo '</table>';

      echo "<table class='tab_cadre_fixe'><thead>";
      echo "<th colspan='4'>" . _x('form_section', 'Generator options', 'dev') . '</th></thead>';
      echo '<td>' . _x('form_field', 'Use unit tests', 'dev') . '</td>';
      echo '<td>';
      Dropdown::showYesNo('use_unit_tests', 1);
      echo '</td>';
      echo '<td>' . _x('form_field', 'Prepare for Plugin Directory and Marketplace', 'dev') . '</td>';
      echo '<td>';
      Dropdown::showYesNo('use_plugin_xml', 1);
      echo '</td>';
      echo '</tr>';
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

   public static function initPlugin(array $options)
   {
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
      $p['language_short'] = explode('_', $p['language'])[0];
      $p['authors'] = array_map('trim', explode(',', $p['authors']));

      // Set the plugins path
      // Note: Always use plugin directory and not marketplace
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
      self::initHooks($plugin_dir, $p);

      // Create setup.php
      self::initSetup($plugin_dir, $p);

      // Init tests if that generator option was selected
      if ($p['use_unit_tests']) {
         self::initTests($plugin_dir, $p);
      }

      if ($p['use_plugin_xml']) {
         self::initPluginXml($plugin_dir, $p);
      }

      // Add common directories
      self::initStructure($plugin_dir, $p);

      return true;
   }

   private static function initStructure($plugin_dir, $options)
   {
      $common_dirs = ['ajax', 'css', 'front', 'inc', 'js'];
      foreach ($common_dirs as $dir) {
         if (!mkdir($plugin_dir . '/' . $dir) && !is_dir($plugin_dir . '/' . $dir)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $plugin_dir . '/' . $dir));
         }
      }
   }

   private static function initSetup($plugin_dir, $options)
   {
      $init_func = new GlobalFunction("plugin_init_{$options['identifier']}");
      $init_func->setBody(<<<EOF
global \$PLUGIN_HOOKS;
\$PLUGIN_HOOKS['csrf_compliant']['{$options['identifier']}'] = true;
EOF
      );
      $uc_identifier = strtoupper($options['identifier']);
      $version_func = new GlobalFunction("plugin_version_{$options['identifier']}");
      $version_func->setBody(<<<EOF
return [
      'name'         => __('{$options['name']}', '{$options['identifier']}'),
      'version'      => PLUGIN_{$uc_identifier}_VERSION,
      'author'       => '{$options['author']}',
      'license'      => '{$options['license']}',
      'homepage'     =>'{$options['homepage']}',
      'requirements' => [
         'glpi'   => [
            'min' => PLUGIN_{$uc_identifier}_MIN_GLPI,
            'max' => PLUGIN_{$uc_identifier}_MAX_GLPI
         ]
      ]
   ];
EOF
      );
      $prerequisites_func = new GlobalFunction("plugin_{$options['identifier']}_check_prerequisites");
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
      $checkconfig_func = new GlobalFunction("plugin_{$options['identifier']}_check_config");
      $checkconfig_func->setBody('return true;');
      $setup_file = fopen($plugin_dir . '/setup.php', 'wb+');
      fwrite($setup_file, '<?php' . PHP_EOL . PHP_EOL);
      fwrite($setup_file, "define('PLUGIN_{$uc_identifier}_VERSION', '{$options['version']}');" . PHP_EOL);
      fwrite($setup_file, "define('PLUGIN_{$uc_identifier}_MIN_GLPI', '{$options['min_glpi']}');" . PHP_EOL);
      fwrite($setup_file, "define('PLUGIN_{$uc_identifier}_MAX_GLPI', '{$options['max_glpi']}');" . PHP_EOL . PHP_EOL);
      fwrite($setup_file, $init_func . PHP_EOL);
      fwrite($setup_file, $version_func . PHP_EOL);
      fwrite($setup_file, $prerequisites_func . PHP_EOL);
      fwrite($setup_file, $checkconfig_func . PHP_EOL);
      fclose($setup_file);
      chmod($plugin_dir . '/setup.php', 0660);
   }

   private static function initHooks($plugin_dir, $options)
   {
      $install_func = new GlobalFunction("plugin_{$options['identifier']}_install");
      $install_func->setBody('return true;');
      $uninstall_func = new GlobalFunction("plugin_{$options['identifier']}_uninstall");
      $uninstall_func->setBody('return true;');
      $hook_file = fopen($plugin_dir . '/hook.php', 'wb+');
      fwrite($hook_file, '<?php' . PHP_EOL);
      fwrite($hook_file, $install_func . PHP_EOL);
      fwrite($hook_file, $uninstall_func . PHP_EOL);
      fclose($hook_file);
      chmod($plugin_dir . '/hook.php', 0660);
   }

   private static function initTests($plugin_dir, $options)
   {
      if (!mkdir($plugin_dir . '/tests') && !is_dir($plugin_dir . '/tests')) {
         throw new \RuntimeException(sprintf('Directory "%s" was not created', $plugin_dir . '/tests'));
      }
      if (!mkdir($plugin_dir . '/tests/units') && !is_dir($plugin_dir . '/tests/units')) {
         throw new \RuntimeException(sprintf('Directory "%s" was not created', $plugin_dir . '/tests/units'));
      }
      $bootstrap_file = fopen($plugin_dir . '/tests/bootstrap.php', 'wb+');
      fwrite($bootstrap_file, <<<EOF
<?php

global \$CFG_GLPI;
define('GLPI_ROOT', dirname(dirname(dirname(__DIR__))));
define("GLPI_CONFIG_DIR", GLPI_ROOT . "/tests");
include GLPI_ROOT . "/inc/includes.php";
include_once GLPI_ROOT . '/tests/GLPITestCase.php';
include_once GLPI_ROOT . '/tests/DbTestCase.php';
\$plugin = new \Plugin();
\$plugin->checkStates(true);
\$plugin->getFromDBbyDir('{$options['identifier']}');
if (!plugin_{$options['identifier']}_check_prerequisites()) {
  echo "\\nPrerequisites are not met!";
  die(1);
}
if (!\$plugin->isInstalled('{$options['identifier']}')) {
  \$plugin->install(\$plugin->getID());
}
if (!\$plugin->isActivated('{$options['identifier']}')) {
  \$plugin->activate(\$plugin->getID());
}
EOF
      );
      fclose($bootstrap_file);
   }

   private static function initPluginXml($plugin_dir, $options)
   {
      $xml = new SimpleXMLElement('<xml/>');
      $root = $xml->addChild('root');
      $root->addChild('name', $options['name']);
      $root->addChild('key', $options['identifier']);
      $root->addChild('state', 'stable');
      $root->addChild('logo');
      $description = $root->addChild('description');
      $short_desc = $description->addChild('short');
      $short_desc->addChild($options['language_short'], $options['description_short']);
      $long_desc = $description->addChild('long');
      $long_desc->addChild($options['language_short'], $options['description_long']);
      $root->addChild('homepage', $options['homepage']);

      // Placeholders
      $root->addChild('download', $options['download']);
      $root->addChild('issues', $options['issues']);
      $root->addChild('readme', $options['readme']);

      $authors = $root->addChild('authors');
      foreach ($options['authors'] as $author) {
         $authors->addChild('author', $author);
      }

      $versions = $root->addChild('versions');
      $base_version = $versions->addChild('version');
      $base_version->addChild('num', $options['version']);
      $base_version->addChild('compatibility', '>=' . $options['min_glpi'] . ' <' . $options['max_glpi']);

      $langs = $root->addChild('langs');
      $langs->addChild('lang', $options['language']);

      $root->addChild('license', $options['license']);

      // Placeholders
      $tags = $root->addChild('tags');
      $tags->addChild($options['language_short']);
      $root->addChild('screenshots');

      $plugin_xml_file = fopen($plugin_dir . "/{$options['identifier']}.xml", 'wb+');
      fwrite($plugin_xml_file, $xml->asXML());
      fclose($plugin_xml_file);
      chmod($plugin_dir . '/hook.php', 0660);
   }
}