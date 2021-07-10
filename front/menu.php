<?php

include('../../../inc/includes.php');

Html::header(_x('plugin_info', 'GLPI Development Helper', 'dev'), '', 'plugins', 'PluginDevMenu');

global $CFG_GLPI;

$links = [];
if (Session::haveRight('config', UPDATE) && $_SESSION['glpi_use_mode'] == Session::DEBUG_MODE) {
   $links[] = Html::link(_x('action', PluginDevAudit::getTypeName(), 'dev'),
      "{$CFG_GLPI['root_doc']}/plugins/dev/front/audit.php");
   $links[] = Html::link(_x('action', PluginDevClassviewer::getTypeName(), 'dev'),
      "{$CFG_GLPI['root_doc']}/plugins/dev/front/classviewer.php");
   $links[] = Html::link(_x('action', PluginDevDbschema::getTypeName(), 'dev'),
      "{$CFG_GLPI['root_doc']}/plugins/dev/front/dbschema.php");
   $links[] = Html::link(_x('action', PluginDevPlugincreator::getTypeName(), 'dev'),
      "{$CFG_GLPI['root_doc']}/plugins/dev/front/plugincreator.form.php");
   $links[] = Html::link(_x('action', PluginDevThemedesigner::getTypeName(), 'dev'),
      "{$CFG_GLPI['root_doc']}/plugins/dev/front/themedesigner.php");
   $links[] = Html::link(_x('action', PluginDevProfiler::getTypeName(), 'dev'),
      "{$CFG_GLPI['root_doc']}/plugins/dev/front/profiler.php");
}

if (count($links)) {
   echo "<div class='center'><table class='tab_cadre plugin-dev-menu'>";
   echo "<thead><th>"._x('plugin_info', 'GLPI Development Helper', 'dev')."</th></thead>";
   echo "<tbody>";
   foreach ($links as $link) {
      echo "<tr><td>{$link}</td></tr>";
   }
   echo "</tbody></table></div>";
} else {
   echo "<div class='center warning' style='width: 40%; margin: auto;'>";
   echo "<i class='fa fa-exclamation-triangle fa-3x'></i>";
   echo "<p>"._x('error', 'You do not have access to development tools', 'dev')."</p>";
   echo "</div>";
}
Html::footer();
